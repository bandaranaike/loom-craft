<?php

return [
    'api_key' => env('DIALOG_ESMS_API_KEY'),
    'base_url' => env('DIALOG_ESMS_BASE_URL', 'https://e-sms.dialog.lk/api/v1/message-via-url'),
    'callback_url' => env('DIALOG_ESMS_CALLBACK_URL'),
    'source_address' => env('DIALOG_ESMS_SOURCE_ADDRESS', 'LoomCraft'),
];
