<?php

namespace App\View\Components;

use Closure;
use App\Models\MusicOnHold;
use App\Models\Recording;
use App\Models\Stream;
use App\Models\Variable; 
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Session;
use Illuminate\View\Component;

class SwitchRingBack extends Component
{
    public $name;
    public $selected;
    public $options;

    public function __construct($name = "ring_group_ringback", $selected = null)
    {
        $this->name = $name;
        $this->selected = $selected ?: '${us-ring}';

        $this->options = [];

        $this->addMusicOnHoldOptions();
        
        $this->addRecordingOptions();
        
        $this->addStreamOptions();
        
        $this->addRingtoneOptions();
        
        $this->addToneOptions();

        $this->options = json_decode(json_encode($this->options));
    }

    private function addMusicOnHoldOptions()
    {
        $mohs = MusicOnHold::where(function($query) {
                $query->where("domain_uuid", Session::get("domain_uuid"))
                      ->orWhereNull("domain_uuid");
            })->get();

        if ($mohs->isNotEmpty()) {
            $values = [];
            $previous_name = "";

            foreach($mohs as $moh) {
                if($previous_name != $moh->music_on_hold_name) {
                    $name = "";
                    
                    if(!empty($moh->domain_uuid)) {
                        $name = $moh->domain->domain_name . '/';
                    }
                    
                    $name .= $moh->music_on_hold_name;

                    $values[] = [
                        "id" => "local_stream://" . $name,
                        "name" => $moh->music_on_hold_name
                    ];
                }
                $previous_name = $moh->music_on_hold_name;
            }

            if (!empty($values)) {
                $this->options[] = [
                    "label" => __("Music on Hold"),
                    "values" => $values
                ];
            }
        }
    }

    private function addRecordingOptions()
    {
        $recordings = Recording::where("domain_uuid", Session::get("domain_uuid"))->get();
        
        if ($recordings->isNotEmpty()) {
            $values = [];
            $recordingsDir = Session::get("switch.recordings.dir", "/var/lib/freeswitch/recordings");
            $domainName = Session::get("domain_name");

            foreach($recordings as $recording) {
                $values[] = [
                    "id" => $recordingsDir . '/' . $domainName . "/" . $recording->recording_filename,
                    "name" => $recording->recording_filename
                ];
            }

            $this->options[] = [
                "label" => __("Recordings"),
                "values" => $values
            ];
        }
    }

    private function addStreamOptions()
    {
        if (class_exists('App\Models\Stream')) {
            $streams = Stream::where(function ($query) {
                    $query->where("domain_uuid", Session::get("domain_uuid"))
                          ->orWhereNull("domain_uuid");
                })
                ->where("stream_enabled", "true")
                ->orderBy("stream_name", "asc")
                ->get();

            if ($streams->isNotEmpty()) {
                $values = [];

                foreach($streams as $stream) {
                    $values[] = [
                        "id" => $stream->stream_location,
                        "name" => $stream->stream_name
                    ];
                }

                $this->options[] = [
                    "label" => __("Streams"),
                    "values" => $values
                ];
            }
        }
    }

    private function addRingtoneOptions()
    {
        $ringtones = Variable::where('var_category', 'Ringtones')
                           ->orderBy('var_name', 'asc')
                           ->get();

        if ($ringtones->isNotEmpty()) {
            $values = [];

            foreach($ringtones as $ringtone) {
                $label = __('label-' . $ringtone->var_name) ?? $ringtone->var_name;
                
                $values[] = [
                    "id" => '${' . $ringtone->var_name . '}',
                    "name" => $label
                ];
            }

            $values[] = [
                "id" => "silence",
                "name" => __("Silence")
            ];

            $this->options[] = [
                "label" => __("Ringtones"),
                "values" => $values
            ];
        }
    }

    private function addToneOptions()
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

        $values = [];
        foreach($tones as $key => $name) {
            $values[] = [
                "id" => '${' . $key . '}',
                "name" => $name
            ];
        }

        $this->options[] = [
            "label" => __("Tones"),
            "values" => $values
        ];
    }

    public function render(): View|Closure|string
    {
        return view('components.switch-ring-back');
    }
}