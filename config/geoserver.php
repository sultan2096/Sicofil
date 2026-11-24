<?php

return [

    /*
    |--------------------------------------------------------------------------
    | GeoServer Configuration (WMS / WFS / Vector Tiles)
    |--------------------------------------------------------------------------
    */

    'enabled' => env('GEOSERVER_ENABLED', false),

    'base_url' => env('GEOSERVER_BASE_URL', 'http://localhost:8081/geoserver'),

    'username' => env('GEOSERVER_USERNAME', 'admin'),

    'password' => env('GEOSERVER_PASSWORD', 'geoserver'),

    'workspace' => env('GEOSERVER_WORKSPACE', 'sicofil'),

    'srs' => 'EPSG:4326',

    'datastore' => [
        'name' => 'sicofil_postgis',
        'type' => 'PostGIS',
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', 5432),
        'database' => env('DB_DATABASE', 'sicosa_postgis'),
        'schema' => 'public',
        'user' => env('DB_USERNAME'),
        'passwd' => env('DB_PASSWORD'),
    ],

    'layers' => [
        'zones_fiscales' => 'zones_fiscales',
        'unites_commerciales' => 'unites_commerciales',
    ],

    'wms' => [
        'version' => '1.3.0',
        'format' => 'image/png',
    ],

    'wfs' => [
        'version' => '2.0.0',
        'output_format' => 'application/json',
    ],

];
