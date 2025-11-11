<?php
namespace App\Http\Controllers;

use App\Services\FileURLRouteService;

class FileURLRouteController extends Controller
{
	private $fileURLRouteService;

	public function __construct(FileURLRouteService $fileURLRouteService)
	{
		$this->fileURLRouteService = $fileURLRouteService;
	}

	public function handle($path = null)
    {
        return $this->fileURLRouteService->resolve($path);
    }

	public function create()
	{

	}

	public function store()
	{

	}

    public function show()
    {
        //
    }

	public function edit()
	{

	}

	public function update()
	{

	}

    public function destroy()
    {

    }
}
