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
        return $this->fileURLRouteService->read($path);
    }

	public function update(Request $request, $path = null)
	{
		return $this->fileURLRouteService->update($request, $path);
	}

	public function delete($path = null)
	{
		return $this->fileURLRouteService->delete($path);
	}
}
