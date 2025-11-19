<?php

namespace App\Http\Controllers;

use App\Models\ViewCallRecording;
use App\Services\AudioPlayDownloadService;

class ViewCallRecordingController extends Controller
{
    private $audioPlayDownloadService;

    public function __construct(AudioPlayDownloadService $audioPlayDownloadService)
    {
        $this->audioPlayDownloadService = $audioPlayDownloadService;
    }

    public function index()
    {
        return view('pages.callrecordings.index');
    }

    public function play($callRecording)
    {
        if (auth()->user()->hasPermission('call_recording_play'))
        {
            $callRecording = ViewCallRecording::where("call_recording_uuid", $callRecording)->first();

    		$path = $callRecording->call_recording_path . "/" . $callRecording->call_recording_name;

            return $this->audioPlayDownloadService->play($path);
		}
    }

    public function download($callRecording)
    {
        if (auth()->user()->hasPermission('call_recording_download'))
        {
            $callRecording = ViewCallRecording::where("call_recording_uuid", $callRecording)->first();

    		$path = $callRecording->call_recording_path . "/" . $callRecording->call_recording_name;

            return $this->audioPlayDownloadService->download($path);
        }
    }
}
