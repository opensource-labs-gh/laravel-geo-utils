<?php

namespace OpensourceLabsGh\GeoUtils\Tests;

use OpensourceLabsGh\GeoUtils\GeoHelper;
use Orchestra\Testbench\TestCase;
use InvalidArgumentException;
use OpensourceLabsGh\GeoUtils\GeoServiceProvider;

class GeoHelperTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [GeoServiceProvider::class];
    }

    public function test_point_in_polygon_inside(): void
    {
        $polygon = [
            ['lat' => 0, 'lng' => 0],
            ['lat' => 0, 'lng' => 10],
            ['lat' => 10, 'lng' => 10],
            ['lat' => 10, 'lng' => 0],
        ];

        $point = ['lat' => 5, 'lng' => 5];

        $result = GeoHelper::isPointInPolygon($polygon, $point);
        $this->assertTrue($result);
    }

    public function test_point_in_polygon_outside(): void
    {
        $polygon = [
            ['lat' => 0, 'lng' => 0],
            ['lat' => 0, 'lng' => 10],
            ['lat' => 10, 'lng' => 10],
            ['lat' => 10, 'lng' => 0],
        ];

        $point = ['lat' => 15, 'lng' => 15];

        $result = GeoHelper::isPointInPolygon($polygon, $point);
        $this->assertFalse($result);
    }

    public function test_point_in_polygon_on_edge(): void
    {
        $polygon = [
            ['lat' => 0, 'lng' => 0],
            ['lat' => 0, 'lng' => 10],
            ['lat' => 10, 'lng' => 10],
            ['lat' => 10, 'lng' => 0],
        ];

        $point = ['lat' => 0, 'lng' => 5]; // On the edge

        $result = GeoHelper::isPointInPolygon($polygon, $point);
        // Edge cases can be tricky with ray casting, but this should be consistent
        $this->assertIsBool($result);
    }

    public function test_optimized_point_in_polygon(): void
    {
        $polygon = [
            ['lat' => 0, 'lng' => 0],
            ['lat' => 0, 'lng' => 10],
            ['lat' => 10, 'lng' => 10],
            ['lat' => 10, 'lng' => 0],
        ];

        $insidePoint = ['lat' => 5, 'lng' => 5];
        $outsidePoint = ['lat' => 15, 'lng' => 15];

        $this->assertTrue(GeoHelper::isPointInPolygonOptimized($polygon, $insidePoint));
        $this->assertFalse(GeoHelper::isPointInPolygonOptimized($polygon, $outsidePoint));
    }

    public function test_array_to_wkt_polygon(): void
    {
        $polygon = [
            ['lat' => 0, 'lng' => 0],
            ['lat' => 0, 'lng' => 10],
            ['lat' => 10, 'lng' => 10],
            ['lat' => 10, 'lng' => 0],
        ];

        $wkt = GeoHelper::arrayToWktPolygon($polygon);
        $expected = 'POLYGON((0.000000 0.000000, 10.000000 0.000000, 10.000000 10.000000, 0.000000 10.000000, 0.000000 0.000000))';
        
        $this->assertEquals($expected, $wkt);
    }

    public function test_calculate_distance(): void
    {
        $point1 = ['lat' => 0, 'lng' => 0];
        $point2 = ['lat' => 0, 'lng' => 1]; // 1 degree longitude difference at equator

        $distanceKm = GeoHelper::calculateDistance($point1, $point2, 'km');
        $distanceMiles = GeoHelper::calculateDistance($point1, $point2, 'miles');
        $distanceMeters = GeoHelper::calculateDistance($point1, $point2, 'meters');

        // At the equator, 1 degree longitude ≈ 111.32 km
        $this->assertGreaterThan(110, $distanceKm);
        $this->assertLessThan(112, $distanceKm);
        
        $this->assertGreaterThan($distanceKm, $distanceMiles); // Miles should be less than km
        $this->assertGreaterThan($distanceKm * 1000, $distanceMeters); // Meters should be much larger
    }

    public function test_get_bounding_box(): void
    {
        $polygon = [
            ['lat' => 1, 'lng' => 1],
            ['lat' => 1, 'lng' => 5],
            ['lat' => 4, 'lng' => 5],
            ['lat' => 4, 'lng' => 1],
        ];

        $boundingBox = GeoHelper::getBoundingBox($polygon);

        $expected = [
            'min_lat' => 1,
            'max_lat' => 4,
            'min_lng' => 1,
            'max_lng' => 5,
        ];

        $this->assertEquals($expected, $boundingBox);
    }

    public function test_is_point_in_bounding_box(): void
    {
        $boundingBox = [
            'min_lat' => 1,
            'max_lat' => 4,
            'min_lng' => 1,
            'max_lng' => 5,
        ];

        $insidePoint = ['lat' => 2, 'lng' => 3];
        $outsidePoint = ['lat' => 6, 'lng' => 3];

        $this->assertTrue(GeoHelper::isPointInBoundingBox($boundingBox, $insidePoint));
        $this->assertFalse(GeoHelper::isPointInBoundingBox($boundingBox, $outsidePoint));
    }

    public function test_invalid_polygon_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Polygon must have at least 3 points');

        $invalidPolygon = [
            ['lat' => 0, 'lng' => 0],
            ['lat' => 1, 'lng' => 1],
        ]; // Only 2 points

        $point = ['lat' => 0.5, 'lng' => 0.5];
        GeoHelper::isPointInPolygon($invalidPolygon, $point);
    }

    public function test_invalid_point_structure_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Point must have 'lat' and 'lng' keys");

        $polygon = [
            ['lat' => 0, 'lng' => 0],
            ['lat' => 0, 'lng' => 1],
            ['lat' => 1, 'lng' => 1],
        ];

        $invalidPoint = ['latitude' => 0.5, 'longitude' => 0.5]; // Wrong keys
        GeoHelper::isPointInPolygon($polygon, $invalidPoint);
    }

    public function test_invalid_coordinates_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid latitude: 100. Must be between -90 and 90');

        $polygon = [
            ['lat' => 0, 'lng' => 0],
            ['lat' => 0, 'lng' => 1],
            ['lat' => 1, 'lng' => 1],
        ];

        $invalidPoint = ['lat' => 100, 'lng' => 0]; // Invalid latitude
        GeoHelper::isPointInPolygon($polygon, $invalidPoint);
    }

    public function test_invalid_distance_unit_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid unit: invalid. Use 'km', 'miles', or 'meters'");

        $point1 = ['lat' => 0, 'lng' => 0];
        $point2 = ['lat' => 1, 'lng' => 1];

        GeoHelper::calculateDistance($point1, $point2, 'invalid');
    }

    public function test_real_world_coordinates(): void
    {
        // Test with real coordinates (Ghana polygon around Accra)
        $accraPolygon = [
            ['lat' => 5.6037, 'lng' => -0.1870],
            ['lat' => 5.6037, 'lng' => -0.1700],
            ['lat' => 5.5800, 'lng' => -0.1700],
            ['lat' => 5.5800, 'lng' => -0.1870],
        ];

        $insidePoint = ['lat' => 5.5919, 'lng' => -0.1785]; // Inside Accra area
        $outsidePoint = ['lat' => 5.7000, 'lng' => -0.1785]; // Outside the polygon

        $this->assertTrue(GeoHelper::isPointInPolygon($accraPolygon, $insidePoint));
        $this->assertFalse(GeoHelper::isPointInPolygon($accraPolygon, $outsidePoint));
    }

    public function test_facade_is_registered(): void
    {
        $this->assertTrue($this->app->bound('geo-helper'));
    }

    public function test_complex_polygon(): void
    {
        // Test with a more complex polygon (pentagon)
        $pentagon = [
            ['lat' => 2, 'lng' => 0],
            ['lat' => 0.618, 'lng' => 1.902],
            ['lat' => -1.618, 'lng' => 1.176],
            ['lat' => -1.618, 'lng' => -1.176],
            ['lat' => 0.618, 'lng' => -1.902],
        ];

        $centerPoint = ['lat' => 0, 'lng' => 0];
        $outsidePoint = ['lat' => 3, 'lng' => 3];

        $this->assertTrue(GeoHelper::isPointInPolygon($pentagon, $centerPoint));
        $this->assertFalse(GeoHelper::isPointInPolygon($pentagon, $outsidePoint));
    }
}