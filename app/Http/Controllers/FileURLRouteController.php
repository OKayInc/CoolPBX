<?php
namespace App\Http\Controllers;

use App\Services\FileURLRouteService;
use Illuminate\Http\Request;

class FileURLRouteController extends Controller
{
	private $fileURLRouteService;

	public function __construct(FileURLRouteService $fileURLRouteService)
	{
		$this->fileURLRouteService = $fileURLRouteService;
	}

	public function create(Request $request, $path = null)
	{
		return $this->fileURLRouteService->create($request, $path);
	}

	public function read($path = null)
    {
        return $this->fileURLRouteService->get($path);
    }

	public function update(Request $request, $path = null)
	{
		return $this->fileURLRouteService->update($request, $path);
	}

	public function destroy($path = null)
	{
		return $this->fileURLRouteService->destroy($path);
	}
}
