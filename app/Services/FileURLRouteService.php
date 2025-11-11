<?php

namespace App\Services;

use App\Models\Domain;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileURLRouteService
{
    public function resolve(?string $path)
    {
        $path = trim($path, "/");

        $segments = $path ? explode("/", $path) : [];

        if(empty($segments))
        {
            return response()->json(["error" => "Empty path"], 400);
        }

        $firstSegment = $segments[0];
        $basePath = "";
        $subPath = "";

        if(Str::isUuid($firstSegment))
		{
			$domain = Domain::where("domain_uuid", $firstSegment)->first();

            if($domain)
            {
                $basePath = storage_path("app/public/{$domain->domain_name}");

                $subPath = implode("/", array_slice($segments, 1));
            }
		}
        elseif($firstSegment === "sounds")
		{
            $basePath = storage_path("app/public/sounds");

		    $subPath = implode("/", array_slice($segments, 1));
        }
		else
		{
            return response()->json(["error" => "Invalid domain or path"], 404);
        }

        $fullPath = rtrim($basePath . "/" . $subPath, "/");

        if(!file_exists($fullPath))
        {
            return response()->json(["error" => "Not found"], 404);
        }

        if(is_dir($fullPath))
        {
            return $this->listDirContents($fullPath, $subPath);
        }

        if(is_file($fullPath))
        {
            return $this->streamFile($fullPath);
        }
    }

    private function listDirContents($dir)
    {
        $items = scandir($dir);

        $directories = [];
        $files = [];

        foreach($items as $item)
        {
            if(in_array($item, [".", ".."]))
            {
                continue;
            }

            $fullItemPath = "{$dir}/{$item}";

            if(is_dir($fullItemPath))
            {
                $directories[] = $item;
            }

            if(is_file($fullItemPath))
            {
                $files[] = [
                    "name" => $item,
                    "size" => filesize($fullItemPath),
                    "mime" => mime_content_type($fullItemPath),
                ];
            }
        }

        return response()->json([
            "directories" => $directories,
            "files" => $files,
        ]);
    }

    private function streamFile($path)
    {
        return new StreamedResponse(function () use ($path)
        {
            readfile($path);
        }, 200, [
            "Content-Type" => mime_content_type($path),
            "Content-Disposition" => 'inline; filename="' . basename($path) . '"',
        ]);
    }
}
