<?php

namespace App\Repositories;

use App\Models\Recording;
use Illuminate\Support\Facades\Session;

class RecordingRepository
{
    public function getAllForDomain($domainUuid)
    {
        return Recording::where('domain_uuid', $domainUuid)
            ->orderBy('recording_name')
            ->get(['recording_name', 'recording_filename']);
    }


    public function getOptions(): array
    {
        $recordings = Recording::where("domain_uuid", Session::get("domain_uuid"))->get();

        if ($recordings->isEmpty()) {
            return [];
        }

        $values = [];
        $recordingsDir = Session::get("switch.recordings.dir", "/var/lib/freeswitch/recordings");
        $domainName = Session::get("domain_name");

        foreach ($recordings as $recording) {
            $values[] = [
                "id" => $recordingsDir . '/' . $domainName . "/" . $recording->recording_filename,
                "name" => $recording->recording_filename
            ];
        }

        return [
            "label" => __("Recordings"),
            "values" => $values
        ];
    }
}
