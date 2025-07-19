<?php

namespace OpensourceLabsGh\GeoUtils\Console\Commands;

use Illuminate\Console\Command;
use OpensourceLabsGh\GeoUtils\GeoHelper;

class GeoUtilsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'geo-utils:test 
                          {--polygon= : JSON array of polygon coordinates}
                          {--point= : JSON object with lat/lng point}
                          {--distance : Test distance calculation}
                          {--bbox : Test bounding box calculation}';

    /**
     * The console command description.
     */
    protected $description = 'Test GeoUtils functionality from command line';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🌍 GeoUtils Command Line Tester');
        $this->newLine();

        if ($this->option('distance')) {
            $this->testDistanceCalculation();
            return 0;
        }

        if ($this->option('bbox')) {
            $this->testBoundingBox();
            return 0;
        }

        $this->testPointInPolygon();
        return 0;
    }

    /**
     * Test point-in-polygon functionality.
     */
    private function testPointInPolygon(): void
    {
        $polygonJson = $this->option('polygon') ?: $this->getDefaultPolygon();
        $pointJson = $this->option('point') ?: $this->getDefaultPoint();

        try {
            $polygon = json_decode($polygonJson, true, 512, JSON_THROW_ON_ERROR);
            $point = json_decode($pointJson, true, 512, JSON_THROW_ON_ERROR);

            $this->info('Testing Point-in-Polygon:');
            $this->table(['Property', 'Value'], [
                ['Polygon Points', count($polygon)],
                ['Test Point', "({$point['lat']}, {$point['lng']})"],
            ]);

            $result = GeoHelper::isPointInPolygon($polygon, $point);
            $optimizedResult = GeoHelper::isPointInPolygonOptimized($polygon, $point);

            $this->info('Results:');
            $this->table(['Method', 'Result'], [
                ['Standard Algorithm', $result ? '✅ Inside' : '❌ Outside'],
                ['Optimized Algorithm', $optimizedResult ? '✅ Inside' : '❌ Outside'],
            ]);

            // Test WKT conversion
            $wkt = GeoHelper::arrayToWktPolygon($polygon);
            $this->info("WKT Format: {$wkt}");

        } catch (\Exception $e) {
            $this->error("Error: {$e->getMessage()}");
        }
    }

    /**
     * Test distance calculation.
     */
    private function testDistanceCalculation(): void
    {
        $point1 = ['lat' => 37.7749, 'lng' => -122.4194]; // San Francisco
        $point2 = ['lat' => 40.7128, 'lng' => -74.0060];  // New York

        $this->info('Testing Distance Calculation:');
        $this->table(['City', 'Coordinates'], [
            ['San Francisco', "({$point1['lat']}, {$point1['lng']})"],
            ['New York', "({$point2['lat']}, {$point2['lng']})"],
        ]);

        $kmDistance = GeoHelper::calculateDistance($point1, $point2, 'km');
        $milesDistance = GeoHelper::calculateDistance($point1, $point2, 'miles');
        $metersDistance = GeoHelper::calculateDistance($point1, $point2, 'meters');

        $this->info('Distances:');
        $this->table(['Unit', 'Distance'], [
            ['Kilometers', number_format($kmDistance, 2) . ' km'],
            ['Miles', number_format($milesDistance, 2) . ' miles'],
            ['Meters', number_format($metersDistance, 0) . ' m'],
        ]);
    }

    /**
     * Test bounding box calculation.
     */
    private function testBoundingBox(): void
    {
        $polygonJson = $this->option('polygon') ?: $this->getDefaultPolygon();
        
        try {
            $polygon = json_decode($polygonJson, true, 512, JSON_THROW_ON_ERROR);
            
            $this->info('Testing Bounding Box Calculation:');
            $this->info("Polygon has " . count($polygon) . " points");

            $boundingBox = GeoHelper::getBoundingBox($polygon);

            $this->table(['Boundary', 'Value'], [
                ['Min Latitude', $boundingBox['min_lat']],
                ['Max Latitude', $boundingBox['max_lat']],
                ['Min Longitude', $boundingBox['min_lng']],
                ['Max Longitude', $boundingBox['max_lng']],
            ]);

            // Test point in bounding box
            $testPoint = ['lat' => ($boundingBox['min_lat'] + $boundingBox['max_lat']) / 2, 
                         'lng' => ($boundingBox['min_lng'] + $boundingBox['max_lng']) / 2];
            
            $inBoundingBox = GeoHelper::isPointInBoundingBox($boundingBox, $testPoint);
            $this->info("Center point in bounding box: " . ($inBoundingBox ? '✅ Yes' : '❌ No'));

        } catch (\Exception $e) {
            $this->error("Error: {$e->getMessage()}");
        }
    }

    /**
     * Get default polygon for testing (roughly a square around downtown area).
     */
    private function getDefaultPolygon(): string
    {
        return json_encode([
            ['lat' => 5.6037, 'lng' => -0.1870],
            ['lat' => 5.6037, 'lng' => -0.1700],
            ['lat' => 5.5800, 'lng' => -0.1700],
            ['lat' => 5.5800, 'lng' => -0.1870],
            ['lat' => 5.6037, 'lng' => -0.1870], // Close the polygon
        ]);
    }

    /**
     * Get default test point.
     */
    private function getDefaultPoint(): string
    {
        return json_encode(['lat' => 5.5919, 'lng' => -0.1785]); // Point inside the default polygon
    }
}