<?php

namespace OpensourceLabsGh\GeoUtils;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class GeoHelper
{
    /**
     * Check if a point is inside a polygon using the ray-casting algorithm (pure PHP).
     * 
     * @param array $polygon Array of coordinate points [['lat' => float, 'lng' => float], ...]
     * @param array $point Point to check ['lat' => float, 'lng' => float]
     * @return bool True if point is inside polygon
     * @throws InvalidArgumentException
     */
    public static function isPointInPolygon(array $polygon, array $point): bool
    {
        self::validatePolygon($polygon);
        self::validatePoint($point);

        $inside = false;
        $x = $point['lng'];
        $y = $point['lat'];
        $n = count($polygon);

        for ($i = 0, $j = $n - 1; $i < $n; $j = $i++) {
            $xi = $polygon[$i]['lng'];
            $yi = $polygon[$i]['lat'];
            $xj = $polygon[$j]['lng'];
            $yj = $polygon[$j]['lat'];

            $denominator = $yj - $yi;
            if (abs($denominator) < 1e-10) {
                continue;
            }

            $intersect = (($yi > $y) !== ($yj > $y)) &&
                         ($x < ($xj - $xi) * ($y - $yi) / $denominator + $xi);

            if ($intersect) $inside = !$inside;
        }

        return $inside;
    }

    /**
     * Check if a point is inside a polygon using MySQL ST_Contains function.
     * 
     * @param string $polygonWkt WKT format polygon string
     * @param float $lat Latitude
     * @param float $lng Longitude
     * @return bool True if point is inside polygon
     * @throws InvalidArgumentException
     */
    public static function isPointInPolygonMySQL(string $polygonWkt, float $lat, float $lng): bool
    {
        if (empty($polygonWkt)) {
            throw new InvalidArgumentException('Polygon WKT cannot be empty');
        }

        self::validateCoordinates($lat, $lng);

        $pointWkt = sprintf('POINT(%F %F)', $lng, $lat);

        $result = DB::selectOne("SELECT ST_Contains(ST_GeomFromText(?), ST_GeomFromText(?)) AS inside", [
            $polygonWkt,
            $pointWkt
        ]);

        return $result->inside == 1;
    }

    /**
     * Convert array of coordinates to WKT polygon format.
     * 
     * @param array $polygon Array of coordinate points
     * @return string WKT polygon string
     * @throws InvalidArgumentException
     */
    public static function arrayToWktPolygon(array $polygon): string
    {
        self::validatePolygon($polygon);

        $coordinates = [];
        foreach ($polygon as $point) {
            $coordinates[] = sprintf('%F %F', $point['lng'], $point['lat']);
        }

        // Close the polygon if not already closed
        if ($polygon[0] !== $polygon[count($polygon) - 1]) {
            $coordinates[] = sprintf('%F %F', $polygon[0]['lng'], $polygon[0]['lat']);
        }

        return sprintf('POLYGON((%s))', implode(', ', $coordinates));
    }

    /**
     * Calculate the distance between two points using Haversine formula.
     * 
     * @param array $point1 First point ['lat' => float, 'lng' => float]
     * @param array $point2 Second point ['lat' => float, 'lng' => float]
     * @param string $unit Distance unit ('km', 'miles', 'meters')
     * @return float Distance between points
     * @throws InvalidArgumentException
     */
    public static function calculateDistance(array $point1, array $point2, string $unit = 'km'): float
    {
        self::validatePoint($point1);
        self::validatePoint($point2);

        $lat1 = deg2rad($point1['lat']);
        $lng1 = deg2rad($point1['lng']);
        $lat2 = deg2rad($point2['lat']);
        $lng2 = deg2rad($point2['lng']);

        $dlat = $lat2 - $lat1;
        $dlng = $lng2 - $lng1;

        $a = sin($dlat / 2) * sin($dlat / 2) + cos($lat1) * cos($lat2) * sin($dlng / 2) * sin($dlng / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        $earthRadius = match ($unit) {
            'miles' => 3959,
            'meters' => 6371000,
            'km' => 6371,
            default => throw new InvalidArgumentException("Invalid unit: {$unit}. Use 'km', 'miles', or 'meters'")
        };

        return $earthRadius * $c;
    }

    /**
     * Get the bounding box of a polygon.
     * 
     * @param array $polygon Array of coordinate points
     * @return array Bounding box ['min_lat' => float, 'max_lat' => float, 'min_lng' => float, 'max_lng' => float]
     * @throws InvalidArgumentException
     */
    public static function getBoundingBox(array $polygon): array
    {
        self::validatePolygon($polygon);

        $latitudes = array_column($polygon, 'lat');
        $longitudes = array_column($polygon, 'lng');

        return [
            'min_lat' => min($latitudes),
            'max_lat' => max($latitudes),
            'min_lng' => min($longitudes),
            'max_lng' => max($longitudes)
        ];
    }

    /**
     * Check if a point is within the bounding box (quick preliminary check).
     * 
     * @param array $boundingBox Bounding box array
     * @param array $point Point to check
     * @return bool True if point is within bounding box
     * @throws InvalidArgumentException
     */
    public static function isPointInBoundingBox(array $boundingBox, array $point): bool
    {
        self::validatePoint($point);

        $requiredKeys = ['min_lat', 'max_lat', 'min_lng', 'max_lng'];
        foreach ($requiredKeys as $key) {
            if (!array_key_exists($key, $boundingBox)) {
                throw new InvalidArgumentException("Bounding box must contain '{$key}' key");
            }
        }

        return $point['lat'] >= $boundingBox['min_lat'] &&
               $point['lat'] <= $boundingBox['max_lat'] &&
               $point['lng'] >= $boundingBox['min_lng'] &&
               $point['lng'] <= $boundingBox['max_lng'];
    }

    /**
     * Optimized point-in-polygon check with bounding box pre-filtering.
     * 
     * @param array $polygon Array of coordinate points
     * @param array $point Point to check
     * @return bool True if point is inside polygon
     */
    public static function isPointInPolygonOptimized(array $polygon, array $point): bool
    {
        $boundingBox = self::getBoundingBox($polygon);
        
        // Quick bounding box check first
        if (!self::isPointInBoundingBox($boundingBox, $point)) {
            return false;
        }

        // If within bounding box, perform full polygon check
        return self::isPointInPolygon($polygon, $point);
    }

    /**
     * Validate polygon array structure.
     * 
     * @param array $polygon
     * @throws InvalidArgumentException
     */
    private static function validatePolygon(array $polygon): void
    {
        if (count($polygon) < 3) {
            throw new InvalidArgumentException('Polygon must have at least 3 points');
        }

        foreach ($polygon as $index => $point) {
            if (!is_array($point) || !isset($point['lat']) || !isset($point['lng'])) {
                throw new InvalidArgumentException("Invalid point at index {$index}. Point must have 'lat' and 'lng' keys");
            }
            
            self::validateCoordinates($point['lat'], $point['lng']);
        }
    }

    /**
     * Validate point array structure.
     * 
     * @param array $point
     * @throws InvalidArgumentException
     */
    private static function validatePoint(array $point): void
    {
        if (!isset($point['lat']) || !isset($point['lng'])) {
            throw new InvalidArgumentException("Point must have 'lat' and 'lng' keys");
        }

        self::validateCoordinates($point['lat'], $point['lng']);
    }

    /**
     * Validate coordinate values.
     * 
     * @param float $lat Latitude
     * @param float $lng Longitude
     * @throws InvalidArgumentException
     */
    private static function validateCoordinates(float $lat, float $lng): void
    {
        if ($lat < -90 || $lat > 90) {
            throw new InvalidArgumentException("Invalid latitude: {$lat}. Must be between -90 and 90");
        }

        if ($lng < -180 || $lng > 180) {
            throw new InvalidArgumentException("Invalid longitude: {$lng}. Must be between -180 and 180");
        }
    }
}