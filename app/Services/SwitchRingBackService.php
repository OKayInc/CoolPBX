<?php

namespace App\Services;

use App\Repositories\MusicOnHoldRepository;
use App\Repositories\RecordingRepository;
use App\Repositories\StreamRepository;
use App\Repositories\VariableRepository;

class SwitchRingBackService
{
    public function __construct(
        protected MusicOnHoldRepository $mohRepo,
        protected RecordingRepository $recordingRepo,
        protected StreamRepository $streamRepo,
        protected VariableRepository $variableRepo
    ) {}

    public function getOptions(): array
    {
        $options = [];

        if ($moh = $this->mohRepo->getOptions()) {
            $options[] = $moh;
        }
        if ($rec = $this->recordingRepo->getOptions()) {
            $options[] = $rec;
        }
        if ($stream = $this->streamRepo->getOptions()) {
            $options[] = $stream;
        }
        if ($ring = $this->variableRepo->getRingtonesOptions()) {
            $options[] = $ring;
        }

        $options[] = [
            "label" => __("Tones"),
            "values" => $this->getToneOptions()
        ];

        return json_decode(json_encode($options), true);
    }

    private function getToneOptions(): array
    {
        $tones = [
            'us-ring' => __('US Ring'),
            'uk-ring' => __('UK Ring'), 
            'fr-ring' => __('French Ring'),
            'de-ring' => __('German Ring'),
            'it-ring' => __('Italian Ring'),
            'busy' => __('Busy'),
            'reorder' => __('Reorder'),
            'dial-tone' => __('Dial Tone'),
        ];

        return collect($tones)->map(fn($name, $key) => [
            "id" => '${' . $key . '}',
            "name" => $name
        ])->toArray();
    }
}
