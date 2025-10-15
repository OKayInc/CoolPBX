<?php

return [
    'path'              => env('SOUNDS_PATH', '/usr/share/freeswitch/sounds'),
    'default_language'  => env('SOUNDS_LANGUAGE', 'en'),
    'default_dialect'   => env('SOUNDS_DIALECT', 'us'),
    'default_voice'     => env('SOUNDS_VOICE', 'callie'),
    'default_rate'      => env('SOUNDS_RATE', '8000'),
];