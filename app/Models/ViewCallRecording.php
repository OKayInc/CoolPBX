<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ViewCallRecording extends Model
{
    protected $table = 'view_call_recordings';

    public $incrementing = false;

    public $timestamps = false;

    protected $primaryKey = "call_recording_uuid";
}
