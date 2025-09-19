<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CallBroadCastController extends Controller
{
    public function index()
    {
        return view('pages.callBroadCast.index');
    }

    public function create()
    {
        return view('pages.callBroadcast.form');
    }
}
