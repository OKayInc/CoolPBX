<?php

namespace App\Http\Controllers;

use App\Http\Requests\MusicOnHoldRequest;
use App\Models\MusicOnHold;
use App\Services\AudioPlayDownloadService;
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

			// Las 4 frecuencias requeridas por FusionPBX/FreeSWITCH
			$rates = [8000, 16000, 32000, 48000];

			foreach ($rates as $rate) {
				// 1. Definir la carpeta destino: music/{name}/{rate}
				// Nota: Usamos 'public' disk según tu código original ("uploads/...")
				$folderRelative = "uploads/music/{$music_name}/{$rate}";
				$folderAbsolute = storage_path("app/public/") . $folderRelative;

				// Asegurar que el directorio existe
				if (!file_exists($folderAbsolute)) {
					mkdir($folderAbsolute, 0755, true);
				}

				$destinationPath = $folderAbsolute . '/' . $fileName;

				// 2. Ejecutar SOX para convertir
				// -r: rate (frecuencia)
				// -c 1: canales (forzamos mono para telefonía, ahorra espacio y evita problemas)
				$this->convertWithSox($absoluteSourcePath, $destinationPath, $rate);

				// 3. Guardar en Base de Datos
				// Usamos updateOrCreate para evitar duplicados si se sube el mismo nombre
				MusicOnHold::updateOrCreate(
					[
						'music_on_hold_name' => $music_name,
						'music_on_hold_rate' => $rate,
					],
					[
						// La ruta que FusionPBX espera (con la variable $${sounds_dir})
						'music_on_hold_path' => '$${sounds_dir}/music/' . $music_name . '/' . $rate,
						// Aquí podrías agregar otros campos por defecto si son necesarios
					]
				);
			}

			// Limpieza: Borrar el archivo temporal
			Storage::delete($tempPath);

			return redirect()->route('musiconhold.index')
				->with('success', 'Music uploaded and resampled successfully.');
		}
	}


	/**
	 * Ejecuta SoX para convertir el audio
	 */
	private function convertWithSox($source, $destination, $rate)
	{
		// Comando: sox "entrada.wav" -r 8000 -c 1 "salida.wav"
		// Agregamos comillas escapeshellarg para evitar errores con espacios en los nombres
		$cmd = sprintf(
			'sox %s -r %d -c 1 %s',
			escapeshellarg($source),
			$rate,
			escapeshellarg($destination)
		);

		// Ejecutar comando en el sistema operativo
		// Como ya probaste "sox" en tu terminal y funciona, esto debería andar.
		// Redirigimos stderr a stdout para capturar errores si los hay (2>&1)
		$output = shell_exec($cmd . " 2>&1");
		// dd($output);

		// Opcional: Loguear si falla
		if (!file_exists($destination)) {
			\Log::error("Error converting file with SoX: " . $output);
		}
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
