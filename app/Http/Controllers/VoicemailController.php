<?php

namespace App\Http\Controllers;

use App\Repositories\VoicemailRepository;
use Illuminate\View\View;

class VoicemailController extends Controller
{
    protected VoicemailRepository $voicemailRepository;

    public function __construct(VoicemailRepository $voicemailRepository)
    {
        $this->voicemailRepository = $voicemailRepository;
    }
    public function index():View
    {
        return view('pages.voicemails.index');
    }

    public function create():View
    {
        return view('pages.voicemails.form');
    }

    public function edit(string $voicemail_uuid):View
    {
        $voicemail = $this->voicemailRepository->find($voicemail_uuid);
        $voicemailUuid = $voicemail->voicemail_uuid;
        return view('pages.voicemails.form', compact('voicemailUuid'));
    }
}
