<?php

namespace App\Services;

/**
 * CoordinateConversionService
 *
 * Converts survey coordinates between Arc 1950 (UTM Zone 35S / 36S) and WGS84.
 *
 * Ellipsoids:
 *   Clarke 1880 (RGS) — used by Arc 1950 datum
 *   WGS84
 *
 * Datum shift: Molodensky-Badekas 7-parameter (simplified 3-parameter Molodensky)
 * Parameters for Arc 1950 → WGS84 (Zambia) from EPSG Dataset:
 *   dX = -147.0 m, dY = 74.0 m, dZ = -329.0 m
 */
class CoordinateConversionService
{
    // ──────────────────────────────────────────────────────────────────────────
    // Ellipsoid constants
    // ──────────────────────────────────────────────────────────────────────────

    /** Clarke 1880 (RGS) semi-major axis (m) */
    private const CLARKE_A = 6378249.145;

    /** Clarke 1880 (RGS) inverse flattening */
    private const CLARKE_F = 1.0 / 293.465;

    /** WGS84 semi-major axis (m) */
    private const WGS84_A = 6378137.0;

    /** WGS84 inverse flattening */
    private const WGS84_F = 1.0 / 298.257223563;

    // ──────────────────────────────────────────────────────────────────────────
    // UTM constants
    // ──────────────────────────────────────────────────────────────────────────

    private const K0            = 0.9996;
    private const FALSE_EASTING = 500000.0;
    private const FALSE_NORTHING_SOUTH = 10_000_000.0;

    // ──────────────────────────────────────────────────────────────────────────
    // Molodensky shift: Arc 1950 → WGS84 (Zambia)
    // ──────────────────────────────────────────────────────────────────────────

    private const SHIFT_DX = -147.0;
    private const SHIFT_DY =   74.0;
    private const SHIFT_DZ = -329.0;

    // ──────────────────────────────────────────────────────────────────────────
    // Public API
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Convert Arc 1950 UTM easting/northing to WGS84 latitude/longitude.
     *
     * @param  float  $easting   UTM easting (metres)
     * @param  float  $northing  UTM northing (metres, South hemisphere = + 10,000,000)
     * @param  string $zone      '35S' or '36S'
     * @return array{latitude: float, longitude: float}
     */
    public static function convertArc1950ToWGS84(
        float  $easting,
        float  $northing,
        string $zone = '35S'
    ): array {
        $zoneNumber = (int) $zone; // strips trailing letter

        // 1. UTM → geographic in Arc 1950 (Clarke 1880 ellipsoid)
        $geo = self::utmToLatLon(
            $easting, $northing, $zoneNumber, 'S',
            self::CLARKE_A, self::CLARKE_F
        );

        // 2. Molodensky datum shift Arc 1950 → WGS84
        $wgs84 = self::molodenskyShift(
            $geo['lat'], $geo['lon'], 0.0,
            self::CLARKE_A, self::CLARKE_F,
            self::WGS84_A,  self::WGS84_F,
            self::SHIFT_DX, self::SHIFT_DY, self::SHIFT_DZ
        );

        return [
            'latitude'  => round($wgs84['lat'], 8),
            'longitude' => round($wgs84['lon'], 8),
        ];
    }

    /**
     * Convert WGS84 latitude/longitude to Arc 1950 UTM easting/northing.
     *
     * @param  float  $latitude   WGS84 latitude (decimal degrees, negative = South)
     * @param  float  $longitude  WGS84 longitude (decimal degrees)
     * @param  string $zone       '35S' or '36S'
     * @return array{easting: float, northing: float}
     */
    public static function convertWGS84ToArc1950(
        float  $latitude,
        float  $longitude,
        string $zone = '35S'
    ): array {
        $zoneNumber = (int) $zone;

        // 1. Inverse Molodensky WGS84 → Arc 1950
        $arc = self::molodenskyShift(
            $latitude, $longitude, 0.0,
            self::WGS84_A,  self::WGS84_F,
            self::CLARKE_A, self::CLARKE_F,
            -self::SHIFT_DX, -self::SHIFT_DY, -self::SHIFT_DZ
        );

        // 2. Geographic → UTM (Clarke 1880 ellipsoid)
        $utm = self::latLonToUtm(
            $arc['lat'], $arc['lon'], $zoneNumber,
            self::CLARKE_A, self::CLARKE_F
        );

        return [
            'easting'  => round($utm['easting'], 3),
            'northing' => round($utm['northing'], 3),
        ];
    }

    /**
     * Detect the most likely UTM zone for a given longitude.
     */
    public static function detectZone(float $longitude): string
    {
        if ($longitude >= 30.0) return '36S';
        return '35S';
    }

    /**
     * Convert a batch of Arc 1950 points to WGS84.
     *
     * @param  array $points  [ ['easting'=>..., 'northing'=>...], ... ]
     * @param  string $zone
     * @return array          Same array with 'latitude' and 'longitude' added
     */
    public static function convertBatch(array $points, string $zone = '35S'): array
    {
        return array_map(function ($pt) use ($zone) {
            $wgs84 = self::convertArc1950ToWGS84(
                (float) $pt['easting'],
                (float) $pt['northing'],
                $zone
            );
            return array_merge($pt, [
                'latitude'  => $wgs84['latitude'],
                'longitude' => $wgs84['longitude'],
            ]);
        }, $points);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Internal: UTM ↔ Geographic
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Reverse UTM projection: grid → geographic.
     *
     * Based on Bowring (1985) / Snyder (1987) series expansion.
     *
     * @return array{lat: float, lon: float}  Decimal degrees
     */
    private static function utmToLatLon(
        float  $easting,
        float  $northing,
        int    $zoneNumber,
        string $hemisphere,     // 'N' or 'S'
        float  $a,
        float  $f
    ): array {
        $e2   = 2.0 * $f - $f * $f;                 // first eccentricity squared
        $e1sq = $e2 / (1.0 - $e2);                  // second eccentricity squared

        $x = $easting - self::FALSE_EASTING;
        $y = $hemisphere === 'S'
            ? $northing - self::FALSE_NORTHING_SOUTH
            : $northing;

        // Footprint latitude
        $M  = $y / self::K0;
        $mu = $M / ($a * (1.0 - $e2 / 4.0 - 3.0 * $e2 * $e2 / 64.0 - 5.0 * $e2 ** 3 / 256.0));

        $e1 = (1.0 - sqrt(1.0 - $e2)) / (1.0 + sqrt(1.0 - $e2));

        $phi1 = $mu
            + (3.0 * $e1 / 2.0      - 27.0 * $e1 ** 3 / 32.0)  * sin(2.0 * $mu)
            + (21.0 * $e1 ** 2 / 16.0 - 55.0 * $e1 ** 4 / 32.0) * sin(4.0 * $mu)
            + (151.0 * $e1 ** 3 / 96.0)                          * sin(6.0 * $mu)
            + (1097.0 * $e1 ** 4 / 512.0)                        * sin(8.0 * $mu);

        $N1 = $a / sqrt(1.0 - $e2 * sin($phi1) ** 2);
        $T1 = tan($phi1) ** 2;
        $C1 = $e1sq * cos($phi1) ** 2;
        $R1 = $a * (1.0 - $e2) / (1.0 - $e2 * sin($phi1) ** 2) ** 1.5;
        $D  = $x / ($N1 * self::K0);

        $lat = $phi1
            - ($N1 * tan($phi1) / $R1) * (
                $D ** 2 / 2.0
                - (5.0 + 3.0 * $T1 + 10.0 * $C1 - 4.0 * $C1 ** 2 - 9.0 * $e1sq) * $D ** 4 / 24.0
                + (61.0 + 90.0 * $T1 + 298.0 * $C1 + 45.0 * $T1 ** 2 - 252.0 * $e1sq - 3.0 * $C1 ** 2) * $D ** 6 / 720.0
            );

        $lambda0 = deg2rad(($zoneNumber * 6) - 183);

        $lon = $lambda0 + (
            $D
            - (1.0 + 2.0 * $T1 + $C1) * $D ** 3 / 6.0
            + (5.0 - 2.0 * $C1 + 28.0 * $T1 - 3.0 * $C1 ** 2 + 8.0 * $e1sq + 24.0 * $T1 ** 2) * $D ** 5 / 120.0
        ) / cos($phi1);

        return [
            'lat' => rad2deg($lat),
            'lon' => rad2deg($lon),
        ];
    }

    /**
     * Forward UTM projection: geographic → grid.
     *
     * @return array{easting: float, northing: float}
     */
    private static function latLonToUtm(
        float $lat_deg,
        float $lon_deg,
        int   $zoneNumber,
        float $a,
        float $f
    ): array {
        $lat  = deg2rad($lat_deg);
        $lon  = deg2rad($lon_deg);

        $e2   = 2.0 * $f - $f * $f;
        $e1sq = $e2 / (1.0 - $e2);

        $lambda0 = deg2rad(($zoneNumber * 6) - 183);

        $N = $a / sqrt(1.0 - $e2 * sin($lat) ** 2);
        $T = tan($lat) ** 2;
        $C = $e1sq * cos($lat) ** 2;
        $A = cos($lat) * ($lon - $lambda0);

        $M = $a * (
            (1.0 - $e2 / 4.0 - 3.0 * $e2 ** 2 / 64.0 - 5.0 * $e2 ** 3 / 256.0) * $lat
            - (3.0 * $e2 / 8.0 + 3.0 * $e2 ** 2 / 32.0 + 45.0 * $e2 ** 3 / 1024.0) * sin(2.0 * $lat)
            + (15.0 * $e2 ** 2 / 256.0 + 45.0 * $e2 ** 3 / 1024.0)                 * sin(4.0 * $lat)
            - (35.0 * $e2 ** 3 / 3072.0)                                             * sin(6.0 * $lat)
        );

        $easting = self::K0 * $N * (
            $A
            + (1.0 - $T + $C) * $A ** 3 / 6.0
            + (5.0 - 18.0 * $T + $T ** 2 + 72.0 * $C - 58.0 * $e1sq) * $A ** 5 / 120.0
        ) + self::FALSE_EASTING;

        $northing = self::K0 * (
            $M + $N * tan($lat) * (
                $A ** 2 / 2.0
                + (5.0 - $T + 9.0 * $C + 4.0 * $C ** 2) * $A ** 4 / 24.0
                + (61.0 - 58.0 * $T + $T ** 2 + 600.0 * $C - 330.0 * $e1sq) * $A ** 6 / 720.0
            )
        );

        if ($lat_deg < 0.0) {
            $northing += self::FALSE_NORTHING_SOUTH;
        }

        return [
            'easting'  => $easting,
            'northing' => $northing,
        ];
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Internal: Molodensky datum shift
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Standard Molodensky transformation between two geodetic datums.
     *
     * @param  float $lat_deg  Source latitude (decimal degrees)
     * @param  float $lon_deg  Source longitude (decimal degrees)
     * @param  float $h        Ellipsoidal height (metres, usually 0 for 2-D)
     * @param  float $a1  Source ellipsoid semi-major axis
     * @param  float $f1  Source ellipsoid flattening
     * @param  float $a2  Target ellipsoid semi-major axis
     * @param  float $f2  Target ellipsoid flattening
     * @param  float $dx  X shift (metres)
     * @param  float $dy  Y shift (metres)
     * @param  float $dz  Z shift (metres)
     * @return array{lat: float, lon: float}
     */
    private static function molodenskyShift(
        float $lat_deg, float $lon_deg, float $h,
        float $a1, float $f1,
        float $a2, float $f2,
        float $dx, float $dy, float $dz
    ): array {
        $lat = deg2rad($lat_deg);
        $lon = deg2rad($lon_deg);

        $e2 = 2.0 * $f1 - $f1 * $f1;

        $da = $a2 - $a1;   // difference in semi-major axes
        $df = $f2 - $f1;   // difference in flattening

        // Radii of curvature
        $sin_lat = sin($lat);
        $cos_lat = cos($lat);
        $sin_lon = sin($lon);
        $cos_lon = cos($lon);

        $W = sqrt(1.0 - $e2 * $sin_lat * $sin_lat);

        $N  = $a1 / $W;                                    // prime vertical
        $M  = $a1 * (1.0 - $e2) / ($W * $W * $W);         // meridional

        $dlat = (
            -$dx * $sin_lat * $cos_lon
            - $dy * $sin_lat * $sin_lon
            + $dz * $cos_lat
            + $da * ($N * $e2 * $sin_lat * $cos_lat) / $a1
            + $df * ($M / (1.0 - $f1) + $N * (1.0 - $f1)) * $sin_lat * $cos_lat
        ) / ($M + $h);

        $dlon = (
            -$dx * $sin_lon
            + $dy * $cos_lon
        ) / (($N + $h) * $cos_lat);

        return [
            'lat' => $lat_deg + rad2deg($dlat),
            'lon' => $lon_deg + rad2deg($dlon),
        ];
    }
}
