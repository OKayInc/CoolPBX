<?php

namespace App\Repositories;

use App\Models\Recording;

class RecordingRepository
{
    public function getAllForDomain($domainUuid)
    {
        return Recording::where('domain_uuid', $domainUuid)
            ->orderBy('recording_name')
            ->get(['recording_name', 'recording_filename']);
    }
}