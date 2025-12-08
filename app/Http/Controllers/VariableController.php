<?php
namespace App\Http\Controllers;

use App\Http\Requests\VariableRequest;
use App\Models\Variable;
use App\Repositories\VariableRepository;

class VariableController extends Controller
{
	protected $variableRepository;

	public function __construct(VariableRepository $variableRepository)
	{
		$this->variableRepository = $variableRepository;
	}
	public function index()
	{
		return view('pages.variables.index');
	}

	public function create()
	{
		$categories = $this->variableRepository->getCategories();

		return view("pages.variables.form", compact("categories"));
	}

	public function store(VariableRequest $request)
	{
		$variable = $this->variableRepository->create($request->validated());

		return redirect()->route("variables.edit", $variable->var_uuid);
	}

    public function show(Variable $variable)
    {
        //
    }

	public function edit(Variable $variable)
	{
		$categories = $this->variableRepository->getCategories();

		return view("pages.variables.form", compact("variable", "categories"));
	}

	public function update(VariableRequest $request, Variable $variable)
	{
		$this->variableRepository->update($variable, $request->validated());

        return redirect()->route("variables.edit", $variable->var_uuid);
	}

    public function destroy(Variable $variable)
    {
        $this->variableRepository->delete($variable);

        return redirect()->route('variables.index');
    }
}
