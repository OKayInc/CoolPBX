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
    public function getAllSounds(): array
    {
        $sounds = [];

        $sounds['miscellaneous'] = $this->getMiscellaneousSounds();
        $sounds['recordings'] = $this->getRecordings();
        $sounds['phrases'] = $this->getPhrases();
        $sounds['sounds'] = $this->getSoundFiles('en', 'us', 'callie');

        dd( $sounds);

        return $sounds;
    }

    /**
     * Get miscellaneous sounds (say, tone_stream)
     * 
     * @return array
     */
    protected function getMiscellaneousSounds(): array
    {
        if (!$this->isSuperAdmin()) {
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
        return $this->soundFileRepository->getAllSoundFiles($language, $dialect, $voice);
    }

    /**
     * Recursively scan sound directory
     * 
     * @param string $directory
     * @param int $maxDepth
     * @param int $currentDepth
     * @return array
     */
    // protected function scanSoundDirectory($directory, $maxDepth = 3, $currentDepth = 0): array
    // {
    //     $files = [];

    //     if ($currentDepth >= $maxDepth || !File::isDirectory($directory)) {
    //         return $files;
    //     }

    //     try {
    //         $items = File::glob($directory . '/*');

    //         foreach ($items as $item) {
    //             if (File::isDirectory($item)) {
    //                 $dirName = basename($item);
    //                 if (!preg_match('/^\d+$/', $dirName)) {
    //                     $files = array_merge($files, $this->scanSoundDirectory($item, $maxDepth, $currentDepth + 1));
    //                 } else {
    //                     $rateFiles = File::glob($item . '/*.{wav,mp3,ogg}', GLOB_BRACE);
    //                     $files = array_merge($files, $rateFiles);
    //                 }
    //             } elseif ($this->isValidSoundFile($item)) {
    //                 $files[] = $item;
    //             }
    //         }
    //     } catch (\Exception $e) {
    //         \Log::error('Error scanning directory ' . $directory . ': ' . $e->getMessage());
    //     }

    //     return $files;
    // }

    /**
     * Check if file is a valid sound file
     * 
     * @param string $filePath
     * @return bool
     */
    // protected function isValidSoundFile($filePath): bool
    // {
    //     $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    //     return in_array($extension, ['wav', 'mp3', 'ogg', 'aiff', 'au']);
    // }

    /**
     * Format sound name for display
     * 
     * @param string $relativePath
     * @return string
     */
    // protected function formatSoundName($relativePath): string
    // {
    //     $name = pathinfo($relativePath, PATHINFO_FILENAME);

    //     $name = str_replace(['_', '-'], ' ', $name);

    //     $name = ucwords($name);

    //     $directory = dirname($relativePath);
    //     if ($directory !== '.' && $directory !== '') {
    //         $directory = ucwords(str_replace(['_', '-', '/'], [' ', ' ', ' > '], $directory));
    //         $name = $directory . ' > ' . $name;
    //     }

    //     return $name;
    // }

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
