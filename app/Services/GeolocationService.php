<?php

namespace App\Services;

class GeolocationService
{
    /**
     * Earth radius in meters
     */
    private const EARTH_RADIUS_METERS = 6371000;

    /**
     * Calculate Haversine distance between two GPS coordinates in meters.
     */
    public function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(
            pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)
        ));

        return round($angle * self::EARTH_RADIUS_METERS, 2);
    }

    /**
     * Check if a coordinate is within a branch's geofence radius.
     */
    public function checkGeofence(float $userLat, float $userLon, float $branchLat, float $branchLon, int $radiusMeters): array
    {
        $distance = $this->calculateDistance($userLat, $userLon, $branchLat, $branchLon);
        $isInside = $distance <= $radiusMeters;

        return [
            'is_inside' => $isInside,
            'distance_meters' => $distance,
            'allowed_radius' => $radiusMeters,
            'message' => $isInside
                ? "Lokasi valid! Anda berada di dalam radius kantor ({$distance}m)."
                : "Di luar jangkauan! Jarak Anda {$distance}m (Maksimal radius {$radiusMeters}m).",
        ];
    }
}
