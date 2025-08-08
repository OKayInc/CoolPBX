<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Storage;

class SoundFileRepository
{
    public function getAllSoundFiles($language = 'en', $dialect = 'us', $voice = 'callie')
    {
        // Lógica para escanear storage/app/public/sounds/
        // Retornar array con estructura ['name' => ..., 'value' => ...]    
        return [];
    }
}