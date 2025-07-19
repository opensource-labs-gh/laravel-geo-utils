<?php

namespace OpensourceLabsGh\GeoUtils\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool isPointInPolygon(array $polygon, array $point)
 * @method static bool isPointInPolygonMySQL(string $polygonWkt, float $lat, float $lng)
 * @method static string arrayToWktPolygon(array $polygon)
 * @method static float calculateDistance(array $point1, array $point2, string $unit = 'km')
 * @method static array getBoundingBox(array $polygon)
 * @method static bool isPointInBoundingBox(array $boundingBox, array $point)
 * @method static bool isPointInPolygonOptimized(array $polygon, array $point)
 * 
 * @see \OpensourceLabsGh\GeoUtils\GeoHelper
 */
class GeoHelper extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'geo-helper';
    }
}