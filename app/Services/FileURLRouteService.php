<?php

namespace App\Services;

use App\Models\Domain;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileURLRouteService
{
    public function get(?string $path)
    {
        $resolved = $this->resolveBasePath($path);

        if($resolved instanceof \Illuminate\Http\JsonResponse)
        {
            return $resolved;
        }

        [$basePath, $subPath] = $resolved;

        $fullPath = rtrim($basePath . "/" . $subPath, "/");

        if(!file_exists($fullPath))
        {
            return response()->json(["error" => "Not found"], 404);
        }

        if(is_dir($fullPath))
        {
            return $this->listDirContents($fullPath);
        }

        if(is_file($fullPath))
        {
            return $this->streamFile($fullPath);
        }

        return response()->json(["error" => "Invalid path"], 400);
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

    public function create(Request $request, ?string $path)
    {
        $resolved = $this->resolveBasePath($path);

        if($resolved instanceof \Illuminate\Http\JsonResponse)
        {
            return $resolved;
        }

        [$basePath, $subPath] = $resolved;

        $fullPath = rtrim($basePath . "/" . $subPath, "/");

        if($request->hasFile("file"))
        {
            $file = $request->file("file");

            $file->move($fullPath, $file->getClientOriginalName());

            return response()->json(["message" => "File uploaded"]);
        }
        else
        {
            if(!is_dir($fullPath))
            {
                mkdir($fullPath, 0755, true);
            }

            return response()->json(["message" => "Directory created"]);
        }

        return response()->json(["error" => "No file or directory specified"], 400);
    }

    public function update(Request $request, ?string $path)
    {
        $resolved = $this->resolveBasePath($path);

        if($resolved instanceof \Illuminate\Http\JsonResponse)
        {
            return $resolved;
        }

        [$basePath, $subPath] = $resolved;

        $fullPath = rtrim($basePath . "/" . $subPath, "/");

        if(!file_exists($fullPath))
        {
            return response()->json(["error" => "Not found"], 404);
        }

        if($request->has("rename"))
        {
            $newPath = dirname($fullPath) . "/" . $request->get("rename");

            rename($fullPath, $newPath);

            return response()->json(["message" => "Renamed successfully"]);
        }

        return response()->json(["error" => "No action specified"], 400);
    }

    public function destroy(?string $path)
    {
        $resolved = $this->resolveBasePath($path);

        if($resolved instanceof \Illuminate\Http\JsonResponse)
        {
            return $resolved;
        }

        [$basePath, $subPath] = $resolved;

        $fullPath = rtrim($basePath . "/" . $subPath, "/");

        if(!file_exists($fullPath))
        {
            return response()->json(["error" => "Not found"], 404);
        }

        if(is_dir($fullPath))
        {
            $this->deleteDirectory($fullPath);

            return response()->json(["message" => "Directory deleted"]);
        }

        if(is_file($fullPath))
        {
            unlink($fullPath);

            return response()->json(["message" => "File deleted"]);
        }
    }

    private function deleteDirectory($dir)
    {
        $items = array_diff(scandir($dir), ['.', '..']);

        foreach($items as $item)
        {
            $path = "$dir/$item";

            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }

        rmdir($dir);
    }

    private function resolveBasePath(?string $path)
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

        return [$basePath, $subPath];
    }
}
