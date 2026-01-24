<?php

return [
    'time_conditions' => [
        'app_uuid' => env('TIME_CONDITION_APP_UUID', '4b821450-926b-175a-af93-a03c441818b1'),
    ],
    'inbound_route' => [
        'app_uuid' => env('INBOUND_ROUTE_APP_UUID', 'c03b422e-13a8-bd1b-e42b-b6b9b4d27ce4'),
    ],
    'outbound_route' => [
        'app_uuid' => env('OUTBOUND_ROUTE_APP_UUID', '8c914ec3-9fc0-8ab5-4cda-6c9288bdc9a3'),
    ],
    'queue' => [
        'app_uuid' => env('QUEUE_APP_UUID', '16589224-c876-aeb3-f59f-523a1c0801f7'),
    ],
    'ivr_menu' => [
        'app_uuid' => env('IVR_MENU_APP_UUID', 'a5788e9b-58bc-bd1b-df59-fff5d51253ab'),
    ],
    'ring_group' => [
        'app_uuid' => env('RING_GROUP_APP_UUID', '1d61fb65-1eec-bc73-a6ee-a6203b4fe6f2'),
    ],
    'domain_settings' => [
        'app_uuid' => env('DOMAIN_SETTINGS_APP_UUID', 'b31e723a-bf70-670c-a49b-470d2a232f71'),
    ],
    'global_variables' => [
        'app_uuid' => env('GLOBAL_VARIABLES_APP_UUID', '9f356fe7-8cf8-4c14-8fe2-6daf89304458'),
    ],

];
