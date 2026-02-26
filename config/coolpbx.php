<?php

return [
    'call_centers' => [
        'app_uuid' => env('CALL_CENTER_APP_UUID', '95788e50-9500-079e-2807-fd530b0ea370'),
    ],
    'conferences' => [
        'app_uuid' => env('CONFERENCE_APP_UUID', 'b81412e8-7253-91f4-e48e-42fc2c9a38d9'),
    ],
    'conference_controls' => [
        'app_uuid' => env('CONFERENCE_CONTROL_APP_UUID', 'e1ad84a2-79e1-450c-a5b1-7507a043e048'),
        'controls' => [
            [
                'control_name' => 'moderator',
                'control_enabled' => 'true',
                'control_description' => 'Controls for Conference Moderators',
                'details' => [
                    [
                        'control_digits' => '*',
                        'control_action' => 'execute_application',
                        'control_data' => 'lua app/conference_center/resources/scripts/unmute.lua non_moderator',
                        'control_enabled' => 'true',
                    ],
                    [
                        'control_digits' => '*',
                        'control_action' => 'deaf mute',
                        'control_data' => null,
                        'control_enabled' => 'true',
                    ],
                    [
                        'control_digits' => '0',
                        'control_action' => 'execute_application',
                        'control_data' => 'lua app/conference_center/resources/scripts/mute.lua non_moderator',
                        'control_enabled' => 'true',
                    ],
                    [
                        'control_digits' => '1',
                        'control_action' => 'vol talk dn',
                        'control_data' => null,
                        'control_enabled' => 'true',
                    ],
                    [
                        'control_digits' => '2',
                        'control_action' => 'vol talk zero',
                        'control_data' => null,
                        'control_enabled' => 'true',
                    ],
                    [
                        'control_digits' => '3',
                        'control_action' => 'vol talk up',
                        'control_data' => null,
                        'control_enabled' => 'true',
                    ],
                    [
                        'control_digits' => '4',
                        'control_action' => 'vol listen dn',
                        'control_data' => null,
                        'control_enabled' => 'true',
                    ],
                    [
                        'control_digits' => '5',
                        'control_action' => 'vol listen zero',
                        'control_data' => null,
                        'control_enabled' => 'true',
                    ],
                    [
                        'control_digits' => '6',
                        'control_action' => 'vol listen up',
                        'control_data' => null,
                        'control_enabled' => 'true',
                    ],
                    [
                        'control_digits' => '7',
                        'control_action' => 'energy dn',
                        'control_data' => null,
                        'control_enabled' => 'true',
                    ],
                    [
                        'control_digits' => '8',
                        'control_action' => 'energy equ',
                        'control_data' => null,
                        'control_enabled' => 'true',
                    ],
                    [
                        'control_digits' => '9',
                        'control_action' => 'energy up',
                        'control_data' => null,
                        'control_enabled' => 'true',
                    ],
                ],
            ],
            [
                'control_name' => 'page',
                'control_enabled' => 'true',
                'control_description' => 'Controls for Conference Pagers',
                'details' => [],
            ],
            [
                'control_name' => 'default',
                'control_enabled' => 'true',
                'control_description' => 'Default Controls for Conferences',
                'details' => [],
            ]
        ],
    ],
    'domains' => [
        'app_uuid' => env('DOMAIN_APP_UUID', 'b31e723a-bf70-670c-a49b-470d2a232f71'),
    ],
    'event_guard' => [
        'app_uuid' => env('EVENT_GUARD_APP_UUID', 'c5b86612-1514-40cb-8e2c-3f01a8f6f637'),
    ],
    'inbound_routes' => [
        'app_uuid' => env('INBOUND_ROUTE_APP_UUID', 'c03b422e-13a8-bd1b-e42b-b6b9b4d27ce4'),
    ],
    'install' => [
        'app_uuid' => env('INSTALL_APP_UUID', '75507e6e-891e-11e5-af63-feff819cdc9f'),
    ],
    'ivr_menus' => [
        'app_uuid' => env('IVR_MENU_APP_UUID', 'a5788e9b-58bc-bd1b-df59-fff5d51253ab'),
    ],
    'outbound_routes' => [
        'app_uuid' => env('OUTBOUND_ROUTE_APP_UUID', '8c914ec3-9fc0-8ab5-4cda-6c9288bdc9a3'),
    ],
    'time_conditions' => [
        'app_uuid' => env('TIME_CONDITION_APP_UUID', '4b821450-926b-175a-af93-a03c441818b1'),
    ],
    'queues' => [
        'app_uuid' => env('QUEUE_APP_UUID', '16589224-c876-aeb3-f59f-523a1c0801f7'),
    ],
    'ring_groups' => [
        'app_uuid' => env('RING_GROUP_APP_UUID', '1d61fb65-1eec-bc73-a6ee-a6203b4fe6f2'),
    ],

    // INSTALLATION PATHS
    'BSD' => [
        'config_path' => env('INSTALL_CONFIG_PATH', '/usr/local/etc/coolpbx'),
        'config_file' => env('INSTALL_CONFIG_FILE', '/usr/local/etc/coolpbx/config.conf'),
        'document_root' => env('DOCUMENT_ROOT', '/usr/local/www/coolpbx'),
        'old_config_path' => env('OLD_INSTALL_CONFIG_PATH', '/usr/local/etc/fusionpbx'),
        'old_config_file' => env('OLD_INSTALL_CONFIG_FILE', '/usr/local/etc/fusionpbx/config.conf'),
        'old_document_root' => env('OLD_DOCUMENT_ROOT', '/usr/local/www/fusionpbx'),
        'conf_dir' => env('INSTALL_CONF_DIR', '/usr/local/etc/freeswitch'),
        'sounds_dir' => env('INSTALL_SOUNDS_DIR', '/usr/share/freeswitch/sounds'),
        'database_dir' => env('INSTALL_DATABASE_DIR', '/var/lib/freeswitch/db'),
        'recordings_dir' => env('INSTALL_RECORDINGS_DIR', '/var/lib/freeswitch/recordings'),
        'storage_dir' => env('INSTALL_STORAGE_DIR', '/var/lib/freeswitch/storage'),
        'voicemail_dir' => env('INSTALL_VOICEMAIL_DIR', '/var/lib/freeswitch/storage/voicemail'),
        'scripts_dir' => env('INSTALL_SCRIPTS_DIR', '/usr/share/freeswitch/scripts'),
        'php_dir' => PHP_BINDIR,
        'cache_location' => env('INSTALL_CACHE_LOCATION', '/var/cache/coolpbx'),
        'old_cache_location' => env('OLD_INSTALL_CACHE_LOCATION', '/var/cache/fusionpbx'),
    ],
    'LIN' => [
        'config_path' => env('INSTALL_CONFIG_PATH', '/etc/coolpbx'),
        'config_file' => env('INSTALL_CONFIG_FILE', '/etc/coolpbx/config.conf'),
        'document_root' => env('DOCUMENT_ROOT', '/var/www/coolpbx'),
        'old_config_path' => env('OLD_INSTALL_CONFIG_PATH', '/etc/fusionpbx'),
        'old_config_file' => env('OLD_INSTALL_CONFIG_FILE', '/etc/fusionpbx/config.conf'),
        'old_document_root' => env('OLD_DOCUMENT_ROOT', '/var/www/fusionpbx'),
        'conf_dir' => env('INSTALL_CONF_DIR', '/etc/freeswitch'),
        'sounds_dir' => env('INSTALL_SOUNDS_DIR', '/usr/share/freeswitch/sounds'),
        'database_dir' => env('INSTALL_DATABASE_DIR', '/var/lib/freeswitch/db'),
        'recordings_dir' => env('INSTALL_RECORDINGS_DIR', '/var/lib/freeswitch/recordings'),
        'storage_dir' => env('INSTALL_STORAGE_DIR', '/var/lib/freeswitch/storage'),
        'voicemail_dir' => env('INSTALL_VOICEMAIL_DIR', '/var/lib/freeswitch/storage/voicemail'),
        'scripts_dir' => env('INSTALL_SCRIPTS_DIR', '/usr/share/freeswitch/scripts'),
        'php_dir' => PHP_BINDIR,
        'cache_location' => env('INSTALL_CACHE_LOCATION', '/var/cache/coolpbx'),
        'old_cache_location' => env('OLD_INSTALL_CACHE_LOCATION', '/var/cache/fusionpbx'),
    ],
    'WIN' => [
        'config_path' => env('INSTALL_CONFIG_PATH', $_SERVER["DOCUMENT_ROOT"].DIRECTORY_SEPARATOR.'resources'),
        'config_file' => env('INSTALL_CONFIG_FILE', $_SERVER["DOCUMENT_ROOT"].DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'config.conf'),
        'document_root' => env('DOCUMENT_ROOT', $_SERVER["DOCUMENT_ROOT"]),
        'conf_dir' => env('INSTALL_CONF_DIR', $_SERVER['ProgramFiles'].DIRECTORY_SEPARATOR.'freeswitch'.DIRECTORY_SEPARATOR.'conf'),
        'sounds_dir' => env('INSTALL_SOUNDS_DIR', $_SERVER['ProgramFiles'].DIRECTORY_SEPARATOR.'freeswitch'.DIRECTORY_SEPARATOR.'sounds'),
        'database_dir' => env('INSTALL_DATABASE_DIR', $_SERVER['ProgramFiles'].DIRECTORY_SEPARATOR.'freeswitch'.DIRECTORY_SEPARATOR.'db'),
        'recordings_dir' => env('INSTALL_RECORDINGS_DIR', $_SERVER['ProgramFiles'].DIRECTORY_SEPARATOR.'freeswitch'.DIRECTORY_SEPARATOR.'recordings'),
        'storage_dir' => env('INSTALL_STORAGE_DIR', $_SERVER['ProgramFiles'].DIRECTORY_SEPARATOR.'freeswitch'.DIRECTORY_SEPARATOR.'storage'),
        'voicemail_dir' => env('INSTALL_VOICEMAIL_DIR', $_SERVER['ProgramFiles'].DIRECTORY_SEPARATOR.'freeswitch'.DIRECTORY_SEPARATOR.'voicemail'),
        'scripts_dir' => env('INSTALL_SCRIPTS_DIR', $_SERVER['ProgramFiles'].DIRECTORY_SEPARATOR.'freeswitch'.DIRECTORY_SEPARATOR.'scripts'),
        'php_dir' => dirname(PHP_BINARY),
        'cache_location' => env('INSTALL_CACHE_LOCATION', dirname($_SERVER['DOCUMENT_ROOT']).DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'coolpbx'),
        'old_cache_location' => env('OLD_INSTALL_CACHE_LOCATION', dirname($_SERVER['DOCUMENT_ROOT']).DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'fusionpbx'),
    ],
];
