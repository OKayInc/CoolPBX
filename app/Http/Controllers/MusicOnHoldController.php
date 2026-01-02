<?php

namespace App\Http\Controllers;

use App\Http\Requests\MusicOnHoldRequest;
use App\Models\MusicOnHold;
use App\Services\AudioPlayDownloadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

		while (($bytes / $step) > 0.9) {
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

		foreach ($musiconhold as $m) {
			$file_list = [];

			$directory = $this->getActualDirectory($m);

			if (is_dir($directory)) {
				$files = glob($directory . "/*.{mp3,wav}", GLOB_BRACE);

				if (!empty($files)) {
					foreach ($files as $file) {
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

		if ($request->hasFile('music_on_hold_file')) {
			$music_name = $validated['music_on_hold_name'];
			$file = $request->file('music_on_hold_file');

			$fileName = $file->getClientOriginalName();

			$tempPath = $file->storeAs('temp_uploads', $fileName);
			$absoluteSourcePath = storage_path("app/{$tempPath}");

			$originalSampleRate = $this->detectSampleRate($absoluteSourcePath);
			\Log::info("Original sample rate: {$originalSampleRate} Hz");

			$rates = [8000, 16000, 32000, 48000];

			foreach ($rates as $rate) {
				$folderRelative = "uploads/music/{$music_name}/{$rate}";
				$folderAbsolute = storage_path("app/public/") . $folderRelative;

				if (!file_exists($folderAbsolute)) {
					mkdir($folderAbsolute, 0755, true);
				}

				$destinationPath = $folderAbsolute . '/' . $fileName;

				$this->convertWithSox($absoluteSourcePath, $destinationPath, $rate);


				MusicOnHold::updateOrCreate(
					[
						'music_on_hold_name' => $music_name,
						'music_on_hold_rate' => $rate,
					],
					[
						'music_on_hold_path' => '$${sounds_dir}/music/' . $music_name . '/' . $rate,
					]
				);
			}

			Storage::delete($tempPath);

			return redirect()->route('musiconhold.index')
				->with('success', 'Music uploaded and resampled successfully.');
		}
	}


	private function convertWithSox($source, $destination, $rate)
	{
		$cmd = sprintf(
			'sox %s -r %d -c 1 %s',
			escapeshellarg($source),
			$rate,
			escapeshellarg($destination)
		);

		$output = shell_exec($cmd . " 2>&1");

		if (!file_exists($destination)) {
			\Log::error("Error converting file with SoX: " . $output);
		}
	}

	private function detectSampleRate($filePath)
	{
		$cmd = sprintf('sox --i -r %s', escapeshellarg($filePath));
		$output = shell_exec($cmd . " 2>&1");

		$sampleRate = trim($output);

		return (int) $sampleRate;
	}

	public function detectSampleRateAjax(Request $request)
	{
		if ($request->hasFile('audio_file')) {
			$file = $request->file('audio_file');

			$tempPath = $file->storeAs('temp_detection', $file->getClientOriginalName());
			$absolutePath = storage_path("app/{$tempPath}");

			$sampleRate = $this->detectSampleRate($absolutePath);

			Storage::delete($tempPath);

			return response()->json([
				'success' => true,
				'sample_rate' => $sampleRate,
				'sample_rate_khz' => round($sampleRate / 1000, 1)
			]);
		}

		return response()->json([
			'success' => false,
			'message' => 'No file provided'
		], 400);
	}

	public function play(MusicOnHold $musiconhold, $file)
	{
		if (auth()->user()->hasPermission('music_on_hold_view')) {
			$path = $this->getActualDirectory($musiconhold) . '/' . $file;
			
			return $this->audioPlayDownloadService->play($path);
		}
	}

	public function download(MusicOnHold $musiconhold, $file)
	{
		if (auth()->user()->hasPermission('music_on_hold_view')) {
			$path = $this->getActualDirectory($musiconhold) . "/" . $file;

			return $this->audioPlayDownloadService->download($path);
		}
	}
}
