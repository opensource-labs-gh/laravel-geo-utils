# Laravel Geo Utils

[![Latest Version on Packagist](https://img.shields.io/packagist/v/opensource-labs-gh/laravel-geo-utils.svg?style=flat-square)](https://packagist.org/packages/opensource-labs-gh/laravel-geo-utils)
[![Total Downloads](https://img.shields.io/packagist/dt/opensource-labs-gh/laravel-geo-utils.svg?style=flat-square)](https://packagist.org/packages/opensource-labs-gh/laravel-geo-utils)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/opensource-labs-gh/laravel-geo-utils/run-tests?label=tests)](https://github.com/opensource-labs-gh/laravel-geo-utils/actions?query=workflow%3Arun-tests+branch%3Amain)

A comprehensive Laravel utility package for geographic operations, including polygon point checks, distance calculations, and spatial data manipulation. This package provides both pure PHP implementations and MySQL spatial function integration.

## Features

- 🎯 **Point-in-Polygon Detection**: Ray-casting algorithm implementation
- 🚀 **Optimized Performance**: Bounding box pre-filtering for faster checks
- 🗄️ **MySQL Spatial Support**: Leverage MySQL's built-in spatial functions
- 📏 **Distance Calculations**: Haversine formula for accurate distance measurement
- 🔄 **WKT Conversion**: Convert between array and Well-Known Text formats
- ⚡ **Laravel Integration**: Service provider, facade, and Artisan commands
- 🧪 **Comprehensive Testing**: Full test coverage with PHPUnit
- 🛠️ **CLI Tools**: Command-line testing and validation tools

## Installation

Install the package via Composer:

```bash
composer require opensource-labs-gh/laravel-geo-utils
```

The package will automatically register its service provider and facade.

### Publishing Configuration

Optionally, you can publish the configuration file:

```bash
php artisan vendor:publish --provider="OpensourceLabsGh\GeoUtils\GeoServiceProvider" --tag="geo-utils-config"
```

## Usage

### Basic Point-in-Polygon Check

```php
use OpensourceLabsGh\GeoUtils\GeoHelper;
// Or use the facade
use OpensourceLabsGh\GeoUtils\Facades\GeoHelper;

$polygon = [
    ['lat' => 5.6037, 'lng' => -0.1870],
    ['lat' => 5.6037, 'lng' => -0.1700],
    ['lat' => 5.5800, 'lng' => -0.1700],
    ['lat' => 5.5800, 'lng' => -0.1870],
];

$point = ['lat' => 5.5919, 'lng' => -0.1785];

$isInside = GeoHelper::isPointInPolygon($polygon, $point);
// Returns: true
```

### Optimized Point-in-Polygon Check

For better performance with large polygons, use the optimized version that includes bounding box pre-filtering:

```php
$isInside = GeoHelper::isPointInPolygonOptimized($polygon, $point);
```

### MySQL Spatial Functions

Use MySQL's built-in spatial functions for potentially better performance:

```php
$polygonWkt = GeoHelper::arrayToWktPolygon($polygon);
$isInside = GeoHelper::isPointInPolygonMySQL($polygonWkt, 5.5919, -0.1785);
```

### Distance Calculations

Calculate distances between two points using the Haversine formula:

```php
$point1 = ['lat' => 37.7749, 'lng' => -122.4194]; // San Francisco
$point2 = ['lat' => 40.7128, 'lng' => -74.0060];  // New York

$distanceKm = GeoHelper::calculateDistance($point1, $point2, 'km');
$distanceMiles = GeoHelper::calculateDistance($point1, $point2, 'miles');
$distanceMeters = GeoHelper::calculateDistance($point1, $point2, 'meters');

// Results:
// $distanceKm ≈ 4135.46
// $distanceMiles ≈ 2569.46
// $distanceMeters ≈ 4135458.97
```

### Bounding Box Operations

Get the bounding box of a polygon:

```php
$boundingBox = GeoHelper::getBoundingBox($polygon);
// Returns:
// [
//     'min_lat' => 5.5800,
//     'max_lat' => 5.6037,
//     'min_lng' => -0.1870,
//     'max_lng' => -0.1700
// ]

// Quick check if point is within bounding box
$isInBounds = GeoHelper::isPointInBoundingBox($boundingBox, $point);
```

### WKT Conversion

Convert array coordinates to Well-Known Text format:

```php
$wktPolygon = GeoHelper::arrayToWktPolygon($polygon);
// Returns: "POLYGON((-0.187000 5.603700, -0.170000 5.603700, -0.170000 5.580000, -0.187000 5.580000, -0.187000 5.603700))"
```

## Artisan Commands

The package includes a command-line tool for testing and validation:

### Basic Point-in-Polygon Test

```bash
php artisan geo-utils:test
```

### Custom Polygon and Point Test

```bash
php artisan geo-utils:test \
  --polygon='[{"lat":5.6037,"lng":-0.1870},{"lat":5.6037,"lng":-0.1700},{"lat":5.5800,"lng":-0.1700},{"lat":5.5800,"lng":-0.1870}]' \
  --point='{"lat":5.5919,"lng":-0.1785}'
```

### Distance Calculation Test

```bash
php artisan geo-utils:test --distance
```

### Bounding Box Test

```bash
php artisan geo-utils:test --bbox
```

## Configuration

The package includes a configuration file with the following options:

```php
return [
    'default_distance_unit' => 'km',
    'mysql_spatial_enabled' => true,
    'coordinate_precision' => 6,
    'validation' => [
        'strict_coordinates' => true,
        'min_polygon_points' => 3,
    ],
    'performance' => [
        'use_bounding_box_optimization' => true,
        'cache_bounding_boxes' => false,
    ],
];
```

## API Reference

### GeoHelper Methods

| Method | Parameters | Return | Description |
|--------|------------|--------|-------------|
| `isPointInPolygon()` | `array $polygon, array $point` | `bool` | Check if point is inside polygon using ray-casting |
| `isPointInPolygonOptimized()` | `array $polygon, array $point` | `bool` | Optimized point-in-polygon with bounding box pre-check |
| `isPointInPolygonMySQL()` | `string $polygonWkt, float $lat, float $lng` | `bool` | MySQL spatial function point-in-polygon check |
| `calculateDistance()` | `array $point1, array $point2, string $unit` | `float` | Calculate distance between two points |
| `getBoundingBox()` | `array $polygon` | `array` | Get polygon bounding box |
| `isPointInBoundingBox()` | `array $boundingBox, array $point` | `bool` | Check if point is in bounding box |
| `arrayToWktPolygon()` | `array $polygon` | `string` | Convert array coordinates to WKT format |

### Data Formats

**Point Format:**
```php
['lat' => float, 'lng' => float]
```

**Polygon Format:**
```php
[
    ['lat' => float, 'lng' => float],
    ['lat' => float, 'lng' => float],
    ['lat' => float, 'lng' => float],
    // ... minimum 3 points required
]
```

**Bounding Box Format:**
```php
[
    'min_lat' => float,
    'max_lat' => float,
    'min_lng' => float,
    'max_lng' => float
]
```

## Performance Considerations

1. **Bounding Box Optimization**: For large polygons, use `isPointInPolygonOptimized()` which performs a quick bounding box check before the full polygon test.

2. **MySQL Spatial Functions**: When working with large datasets, consider using `isPointInPolygonMySQL()` which leverages MySQL's optimized spatial functions.

3. **Coordinate Precision**: Adjust `coordinate_precision` in config based on your needs - higher precision means more accuracy but potentially slower performance.

## Error Handling

The package validates input data and throws `InvalidArgumentException` for:
- Polygons with fewer than 3 points
- Invalid coordinate values (latitude not between -90 and 90, longitude not between -180 and 180)
- Missing required array keys ('lat', 'lng')
- Invalid distance units
- Empty WKT strings

## Testing

Run the package tests:

```bash
composer test
```

Or run tests with coverage:

```bash
composer test-coverage
```

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details on how to contribute to this project.

## Security

If you discover any security related issues, please email mawulikofiagbenyo@gmail.com instead of using the issue tracker.

## Credits

- [Mawuli Agbenyo](https://github.com/opensource-labs-gh)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Changelog

Please see [CHANGELOG.md](CHANGELOG.md) for more information about recent changes.

## Support

- 📧 Email: mawulikofiagbenyo@gmail.com
- 🐛 Issues: [GitHub Issues](https://github.com/opensource-labs-gh/laravel-geo-utils/issues)
- 💬 Discussions: [GitHub Discussions](https://github.com/opensource-labs-gh/laravel-geo-utils/discussions)

---

Made with ❤️ by [Opensource Labs Ghana](https://github.com/opensource-labs-gh)