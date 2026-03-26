<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Driver Storage Disk
    |--------------------------------------------------------------------------
    |
    | The filesystem disk used for storing report driver YAML files.
    | Configure this disk in config/filesystems.php.
    |
    */

    'driver_disk' => env('REPORT_DRIVER_DISK', 'report-drivers'),

    /*
    |--------------------------------------------------------------------------
    | Cache TTL
    |--------------------------------------------------------------------------
    |
    | How long parsed driver definitions are cached (in seconds).
    | Set to 0 to disable caching.
    |
    */

    'cache_ttl' => env('REPORT_CACHE_TTL', 3600),

    /*
    |--------------------------------------------------------------------------
    | Default Format
    |--------------------------------------------------------------------------
    |
    | The default output format when none is specified.
    |
    */

    'default_format' => env('REPORT_DEFAULT_FORMAT', 'json'),

    /*
    |--------------------------------------------------------------------------
    | Driver Sources
    |--------------------------------------------------------------------------
    |
    | Absolute paths to directories containing report driver YAML files.
    | Packages register their paths here during service provider registration.
    | The report:install-drivers command copies from these sources to the
    | driver_disk storage.
    |
    */

    'driver_sources' => [],

    /*
    |--------------------------------------------------------------------------
    | Template Path
    |--------------------------------------------------------------------------
    |
    | Path to Handlebars templates for HTML report output.
    | Host app can publish and override templates here.
    |
    */

    'template_path' => env('REPORT_TEMPLATE_PATH', resource_path('report-templates')),

];
