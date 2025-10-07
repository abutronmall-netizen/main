<?php

return [
    'base_uri' => env('FNB_BASE_URI'),
    'oauth_uri' => env('FNB_OAUTH_URI'),
    'client_id' => env('FNB_CLIENT_ID'),
    'client_secret' => env('FNB_CLIENT_SECRET'),
    'cert_path' => env('FNB_CERT_PATH'),
    'cert_key_path' => env('FNB_CERT_KEY_PATH'),
    'webhook_secret' => env('FNB_WEBHOOK_SECRET'),
    'webhook_verification' => env('FNB_WEBHOOK_VERIFICATION', 'strict'),
    'timeout' => 10,
    'connect_timeout' => 5,
    'retry_attempts' => 3,
    'retry_delay_ms' => 200,
];
