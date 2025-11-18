<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Would you like the install button to appear on all pages?
      Set true/false
    |--------------------------------------------------------------------------
    */

    'install-button' => false,

    /*
    |--------------------------------------------------------------------------
    | PWA Manifest Configuration
    |--------------------------------------------------------------------------
    |  php artisan erag:update-manifest
    */

    'manifest' => [
        'name' => 'Payroll Management System',
        'short_name' => 'PayrollMS',
        'background_color' => '#f8f8f8',
        'display' => 'fullscreen',
        'description' => 'Progressive Web Application for payroll management.',
        'theme_color' => '#e7e7e7',
        "icons" => [
            [
                'src' => 'images/android/android-launchericon-48-48.png',
                'sizes' => '48x48',
                'type' => 'image/png'
            ],
            [
                'src' => 'images/android/android-launchericon-72-72.png',
                'sizes' => '72x72',
                'type' => 'image/png'
            ],
            [
                'src' => 'images/android/android-launchericon-96-96.png',
                'sizes' => '96x96',
                'type' => 'image/png'
            ],
            [
                'src' => 'images/android/android-launchericon-144-144.png',
                'sizes' => '144x144',
                'type' => 'image/png'
            ],
            [
                'src' => 'images/android/android-launchericon-192-192.png',
                'sizes' => '192x192',
                'type' => 'image/png'
            ],
            [
                'src' => 'images/android/android-launchericon-512-512.png',
                'sizes' => '512x512',
                'type' => 'image/png'
            ]
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Debug Configuration
    |--------------------------------------------------------------------------
    | Toggles the application's debug mode based on the environment variable
    */

    'debug' => env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Livewire Integration
    |--------------------------------------------------------------------------
    | Set to true if you're using Livewire in your application to enable
    | Livewire-specific PWA optimizations or features.
    */

    'livewire-app' => false,
];
