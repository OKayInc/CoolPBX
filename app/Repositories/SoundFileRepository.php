<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Storage;

class SoundFileRepository
{
    public function getAllSoundFiles($language = null, $dialect = null, $voice = null)
    {
        return getSounds($language, $dialect, $voice);
    }
}