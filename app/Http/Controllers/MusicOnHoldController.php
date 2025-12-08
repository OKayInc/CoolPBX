<?php

namespace App\Http\Controllers;

use App\Http\Requests\MusicOnHoldRequest;
use App\Models\MusicOnHold;
use App\Services\AudioPlayDownloadService;

class MusicOnHoldController extends Controller
{
    private $audioPlayDownloadService;

    public function __construct(AudioPlayDownloadService $audioPlayDownloadService)
    {
        $this->audioPlayDownloadService = $audioPlayDownloadService;
    }

	private function byte_convert($bytes, $precision = 2)
	{
		static $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];

		$step = 1024;

		$i = 0;

		while(($bytes / $step) > 0.9)
		{
			$bytes = $bytes / $step;

			$i++;
		}

		return round($bytes, $precision) . ' ' . $units[$i];
	}

	private function getActualDirectory(MusicOnHold $musiconhold)
	{
        //TODO: get directory from settings service
		// $sound_directory = "/usr/share/freeswitch/sounds";
		$sound_directory = storage_path("app/public/") . "uploads"; //TODO: get this value from settings service

		return str_replace('$${sounds_dir}', $sound_directory, $musiconhold->music_on_hold_path);
	}

    public function index()
    {
		$musiconhold = MusicOnHold::all();

		$list = [];

        $categories = array_unique($musiconhold->pluck("music_on_hold_name")->toArray());

		foreach($musiconhold as $m)
		{
			$file_list = [];

			$directory = $this->getActualDirectory($m);

			if(is_dir($directory))
			{
				$files = glob($directory . "/*.{mp3,wav}", GLOB_BRACE);

				if(!empty($files))
				{
					foreach($files as $file)
					{
						$file_list[] = [
							"name" => pathinfo($file, PATHINFO_BASENAME),
							"size" => $this->byte_convert(filesize($file)),
							"uploaded" => date("M d, Y H:i:s", filemtime($file)),
						];
					}

					$list[] = [
						"id" => $m->music_on_hold_uuid,
						"name" => $m->music_on_hold_name,
						"rate" => $m->kHz,
						"files" => $file_list
					];
				}
			}
		}

        return view('pages.musiconhold.index', compact('list', 'categories'));
    }

    public function upload(MusicOnHoldRequest $request)
    {
        $validated = $request->validated();

        if($request->hasFile('music_on_hold_file'))
        {
            $music_on_hold_name = $validated['music_on_hold_name'];
            $music_on_hold_rate = $validated['music_on_hold_rate'];
            $folder = "music/{$music_on_hold_name}/{$music_on_hold_rate}";
            $file = $request->file('music_on_hold_file');

            $fileName = $file->getClientOriginalName() . '.' . $file->getClientOriginalExtension();

            //TODO: get directory from settings service
            $file->storeAs('uploads/' . $folder, $fileName, 'public');

            MusicOnHold::firstOrCreate([
                'music_on_hold_name' => $validated['music_on_hold_name'],
                'music_on_hold_rate' => $validated['music_on_hold_rate'],
                'music_on_hold_path' => '$${sounds_dir}/music/' . $validated['music_on_hold_name'] . '/' . $validated['music_on_hold_rate'],
            ]);

            return redirect()->route('musiconhold.index');
        }
    }

	public function play(MusicOnHold $musiconhold, $file)
	{
		if (auth()->user()->hasPermission('music_on_hold_view'))
        {
			$path = $this->getActualDirectory($musiconhold) . '/' . $file;

            return $this->audioPlayDownloadService->play($path);
		}
	}

    public function download(MusicOnHold $musiconhold, $file)
    {
        if (auth()->user()->hasPermission('music_on_hold_view'))
        {
			$path = $this->getActualDirectory($musiconhold) . "/" . $file;

            return $this->audioPlayDownloadService->download($path);
        }
    }
}
