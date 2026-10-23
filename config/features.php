<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Application Feature Flags
    |--------------------------------------------------------------------------
    |
    | Controls UI visibility and optional system features based on environment
    | settings. Default values ensure safe, predictable production behavior.
    |
    */

    'chatbot_ui_enabled' => filter_var(env('CHATBOT_UI_ENABLED', false), FILTER_VALIDATE_BOOLEAN),
];
