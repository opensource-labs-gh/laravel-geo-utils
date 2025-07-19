<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Distance Unit
    |--------------------------------------------------------------------------
    |
    | This value determines the default unit of measurement for distance
    | calculations. Supported units: "km", "miles", "meters"
    |
    */

    'default_distance_unit' => env('GEO_UTILS_DEFAULT_DISTANCE_UNIT', 'km'),

    /*
    |--------------------------------------------------------------------------
    | MySQL Spatial Support
    |--------------------------------------------------------------------------
    |
    | Enable or disable MySQL spatial function support. When enabled, the
    | package will use MySQL's built-in spatial functions for polygon
    | operations when available.
    |
    */

    'mysql_spatial_enabled' => env('GEO_UTILS_MYSQL_SPATIAL_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Coordinate Precision
    |--------------------------------------------------------------------------
    |
    | Number of decimal places to use for coordinate precision in calculations
    | and WKT formatting. Higher values provide more precision but may
    | impact performance.
    |
    */

    'coordinate_precision' => env('GEO_UTILS_COORDINATE_PRECISION', 6),

    /*
    |--------------------------------------------------------------------------
    | Validation Settings
    |--------------------------------------------------------------------------
    |
    | Configure validation behavior for geographic data.
    |
    */

    'validation' => [
        'strict_coordinates' => env('GEO_UTILS_STRICT_COORDINATES', true),
        'min_polygon_points' => env('GEO_UTILS_MIN_POLYGON_POINTS', 3),
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Settings
    |--------------------------------------------------------------------------
    |
    | Configure performance-related options.
    |
    */

    'performance' => [
        'use_bounding_box_optimization' => env('GEO_UTILS_USE_BOUNDING_BOX_OPTIMIZATION', true),
        'cache_bounding_boxes' => env('GEO_UTILS_CACHE_BOUNDING_BOXES', false),
    ],

];