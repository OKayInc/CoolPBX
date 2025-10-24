<?php

namespace App\Services;

use App\Models\Recording;
use App\Models\Phrase;
use App\Repositories\PhraseRepository;
use App\Repositories\RecordingRepository;
use App\Repositories\SoundFileRepository;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class SoundsService
{
    protected $domainUuid;
    protected $soundsPath;
    protected $recordingRepository;
    protected $phraseRepository;
    protected $soundFileRepository;

    public function __construct(
        RecordingRepository $recordingRepository,
        PhraseRepository $phraseRepository,
        SoundFileRepository $soundFileRepository
    ) {
        $this->domainUuid = Auth::user()->domain_uuid ?? null;
        $this->recordingRepository = $recordingRepository;
        $this->phraseRepository = $phraseRepository;
        $this->soundFileRepository = $soundFileRepository;
    }
    /**
     * Get all available sounds organized by category
     * 
     * @return array
     */
    public function getAllSounds(string $language = 'en', string $dialect = 'us', string $voice = 'callie'): array
    {
        $sounds = [];

        $sounds['miscellaneous'] = $this->getMiscellaneousSounds();
        $sounds['recordings'] = $this->getRecordings();
        $sounds['phrases'] = $this->getPhrases();
        $sounds['sounds'] = $this->getSoundFiles($language, $dialect, $voice);


        return $sounds;
    }

    /**
     * Get miscellaneous sounds (say, tone_stream)
     * 
     * @return array
     */
    protected function getMiscellaneousSounds(): array
    {
        if (!Auth::user()->hasGroup('superadmin')) {
            return [];
        }

        return [
            [
                'name' => 'Say',
                'value' => 'say:',
                'description' => 'Text-to-speech'
            ],
            [
                'name' => 'Tone Stream',
                'value' => 'tone_stream:',
                'description' => 'Generate tones'
            ]
        ];
    }

    /**
     * Get recordings from database
     * 
     * @return array
     */
    protected function getRecordings(): array
    {
        if (!$this->domainUuid) {
            return [];
        }

        $recordings = $this->recordingRepository->getAllForDomain($this->domainUuid);

        return $recordings->map(function ($recording) {
            return [
                'name' => $recording->recording_name,
                'value' => $recording->recording_filename
            ];
        })->toArray();
    }
    /**
     * Get phrases from database
     * 
     * @return array
     */
    protected function getPhrases(): array
    {
        if (!$this->domainUuid) {
            return [];
        }

        $phrases = $this->phraseRepository->getAllForDomain($this->domainUuid);

        return $phrases->map(function ($phrase) {
            return [
                'name' => 'phrase:' . $phrase->phrase_name,
                'value' => 'phrase:' . $phrase->phrase_uuid
            ];
        })->toArray();
    }

    /**
     * Get sound files from filesystem
     * 
     * @param string $language
     * @param string $dialect
     * @param string $voice
     * @return array
     */

    protected function getSoundFiles($language, $dialect, $voice): array
    {
        $soundFiles = $this->soundFileRepository->getAllSoundFiles($language, $dialect, $voice);
        
        return collect($soundFiles)->map(function ($soundFile) {
            $pathParts = explode('/', $soundFile);
            $fileName = end($pathParts);
            $nameWithoutExtension = pathinfo($fileName, PATHINFO_FILENAME);
            
            return [
                'name' => $nameWithoutExtension,
                'value' => $soundFile
            ];
        })->toArray();
    }
    
    /**
     * Check if current user is super admin
     * 
     * @return bool
     */
    protected function isSuperAdmin(): bool
    {
        return Auth::user() && Auth::user()->hasGroup('superadmin');
    }

    /**
     * Get sounds for a specific category
     * 
     * @param string $category
     * @return array
     */
    public function getSoundsByCategory($category): array
    {
        $allSounds = $this->getAllSounds();
        return $allSounds[$category] ?? [];
    }

    /**
     * Search sounds by name
     * 
     * @param string $search
     * @return array
     */
    public function searchSounds($search): array
    {
        $allSounds = $this->getAllSounds();
        $results = [];

        foreach ($allSounds as $category => $sounds) {
            $filtered = array_filter($sounds, function ($sound) use ($search) {
                return stripos($sound['name'], $search) !== false ||
                    stripos($sound['value'], $search) !== false;
            });

            if (!empty($filtered)) {
                $results[$category] = array_values($filtered);
            }
        }

        return $results;
    }

    /**
     * Validate if a sound value exists
     * 
     * @param string $soundValue
     * @return bool
     */
    public function validateSound($soundValue): bool
    {
        if (empty($soundValue)) {
            return true;
        }

        $allSounds = $this->getAllSounds();

        foreach ($allSounds as $sounds) {
            foreach ($sounds as $sound) {
                if ($sound['value'] === $soundValue) {
                    return true;
                }
            }
        }

        return false;
    }
}