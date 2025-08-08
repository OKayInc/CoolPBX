<?php

namespace App\Http\Controllers;

use App\Repositories\RingGroupRepository;
use Illuminate\Http\Request;

class RingGroupController extends Controller
{
    protected $ringGroupRepository;

    public function __construct(RingGroupRepository $ringGroupRepository)
    {
        $this->ringGroupRepository = $ringGroupRepository;
    }

    public function index()
    {
        return view('pages.ringgroup.index');
    }

    public function create()
    {
        return view('pages.ringgroup.form');
    }

    public function edit(string $uuid)
    {
        $ringgroupUuid = $this->ringGroupRepository->findByUuid($uuid);
        return view('pages.ringgroup.form', compact('ringgroupUuid'));
    }
}
