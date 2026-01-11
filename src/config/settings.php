<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Application Demo Mode
    |--------------------------------------------------------------------------
    |
    | Enable or disable demo mode for the application. This is typically
    | controlled via an environment variable (DEMO_ENVIRONMENT). It can be
    | overrides the database setting (via CLI) if present.
    |
    */
    'demo_environment' => (int)env('DEMO_ENVIRONMENT', 1)

];