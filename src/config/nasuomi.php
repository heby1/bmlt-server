<?php

return [
    'source_url' => env('NASUOMI_SOURCE_URL', 'https://www.nasuomi.org/wp-json/wp/v2/kokoukset'),
    'state_dir' => env('NASUOMI_STATE_DIR', dirname(base_path()) . '/nasuomi-state'),
    'admin_username' => env('NASUOMI_ADMIN_USERNAME', 'serveradmin'),
    'initial_admin_password' => env('NASUOMI_INITIAL_ADMIN_PASSWORD'),
    'coordinate_overrides' => env('NASUOMI_COORDINATE_OVERRIDES'),
    'schedule_enabled' => filter_var(env('NASUOMI_SCHEDULE_ENABLED', false), FILTER_VALIDATE_BOOLEAN),
    'sync_time' => env('NASUOMI_SYNC_TIME', '04:15'),
];
