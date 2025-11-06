<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Requests\UserRequest;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;

class UserAPIController extends Controller
{
	protected UserRepository $userRepository;

	public function __construct(UserRepository $userRepository)
	{
		$this->userRepository = $userRepository;
	}

	public function mine(){
        return response()->json(["data" => $this->userRepository->mine()]);
    }

	public function index()
	{
        return response()->json($this->userRepository->all());
	}

    // TODO:
    public function store(UserRequest $request)
	{
		$newUser = $this->userRepository->create($request->validated());
        return response()->json($newUser);
	}

	public function show(User $extension)
	{
		$d = $this->userRepository->findByUuid($extension->domain_uuid, true);
        return response()->json($d);
	}

	public function update(UserRequest $request, User $extension)
	{
		$d = $this->userRepository->update($extension, $request->validated());
		return response()->json($d);
	}

	public function destroy(User $extension)
	{
		$d = $this->userRepository->delete($extension);
        return response()->json($d);
	}
}
