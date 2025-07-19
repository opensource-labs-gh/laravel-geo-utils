# Changelog

All notable changes to `laravel-geo-utils` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.0] - 2025-07-18

### Added
- Initial release of Laravel Geo Utils package
- Point-in-polygon detection using ray-casting algorithm
- MySQL spatial function integration for point-in-polygon checks
- Distance calculation using Haversine formula
- Bounding box calculation and point-in-bounding-box checks
- WKT (Well-Known Text) format conversion
- Optimized point-in-polygon check with bounding box pre-filtering
- Comprehensive input validation with detailed error messages
- Laravel service provider and facade integration
- Artisan command for testing and validation (`geo-utils:test`)
- Full PHPUnit test coverage
- Configuration file with customizable options
- Support for Laravel 9.x, 10.x, 11.x, and 12.x
- Support for PHP 8.1+

### Features
- **Core Functionality:**
  - `isPointInPolygon()` - Pure PHP ray-casting implementation
  - `isPointInPolygonOptimized()` - Performance-optimized version
  - `isPointInPolygonMySQL()` - MySQL spatial function integration
  - `calculateDistance()` - Haversine distance calculation
  - `getBoundingBox()` - Polygon bounding box calculation
  - `isPointInBoundingBox()` - Quick bounding box checks
  - `arrayToWktPolygon()` - Array to WKT conversion

- **Laravel Integration:**
  - Auto-discoverable service provider
  - Facade support for easy access
  - Configuration publishing
  - Artisan command with multiple test modes

- **Validation & Error Handling:**
  - Strict coordinate validation (-90 to 90 for latitude, -180 to 180 for longitude)
  - Minimum polygon point requirements
  - Proper exception handling with descriptive messages

- **Performance Features:**
  - Bounding box optimization for large polygons
  - Configurable coordinate precision
  - Optional MySQL spatial function usage

### Documentation
- Comprehensive README with usage examples
- Full API reference documentation
- Command-line usage examples
- Performance considerations guide
- Contributing guidelines