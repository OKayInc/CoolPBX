<?php

namespace App\Http\Controllers;

use App\Models\XmlCDR;
use App\Services\AudioPlayDownloadService;

class XmlCDRController extends Controller
{
    private $audioPlayDownloadService;

    public function __construct(AudioPlayDownloadService $audioPlayDownloadService)
    {
        $this->audioPlayDownloadService = $audioPlayDownloadService;
    }

    public function index()
    {
        return view('pages.xmlcdr.index');
    }

    public function play(XmlCDR $xmlcdr)
    {
        if (auth()->user()->hasPermission('xml_cdr_view'))
        {
            $path = $xmlcdr->record_path . '/' . $xmlcdr->record_name;

            return $this->audioPlayDownloadService->play($path);
        }
    }

    public function download(XmlCDR $xmlcdr)
    {
        if (auth()->user()->hasPermission('xml_cdr_view'))
        {
            $path = $xmlcdr->record_path . '/' . $xmlcdr->record_name;

            return $this->audioPlayDownloadService->download($path);
        }
    }

    public function filterByStatus(string $status)
    {
        $filters = [
            "direction" => "inbound",
            "status" => $status,
            "type" => [
                "callcenter"
            ],
        ];

        return view('pages.xmlcdr.index', compact("filters"));
    }
}
