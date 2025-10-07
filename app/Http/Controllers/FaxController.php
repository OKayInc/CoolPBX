<?php
namespace App\Http\Controllers;

use App\Http\Requests\FaxRequest;
use App\Models\Fax;
use App\Repositories\FaxRepository;

class FaxController extends Controller
{
	protected $faxRepository;

	public function __construct(FaxRepository $faxRepository)
	{
		$this->faxRepository = $faxRepository;
	}
	public function index()
	{
		return view('pages.faxes.index');
	}

	public function create()
	{
		return view("pages.faxes.form");
	}

	public function store(FaxRequest $request)
	{
		$fax = $this->faxRepository->create($request->validated());

		return redirect()->route("faxes.edit", $fax->fax_uuid);
	}

    public function show(Fax $fax)
    {
        //
    }

	public function edit(Fax $fax)
	{
		return view("pages.faxes.form", compact("fax"));
	}

	public function update(FaxRequest $request, Fax $fax)
	{
		$this->faxRepository->update($fax, $request->validated());

        return redirect()->route("faxes.edit", $fax->fax_uuid);
	}

    public function destroy(Fax $fax)
    {
        $this->faxRepository->delete($fax);

        return redirect()->route('faxes.index');
    }
}
