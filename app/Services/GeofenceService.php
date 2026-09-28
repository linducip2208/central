<?php

namespace App\Services;

/**
 * Geofence Haversine + optimasi urutan stop (nearest-neighbor dari depot).
 */
class GeofenceService
{
    public function distanceMeters(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $r = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;

        return 2 * $r * asin(min(1, sqrt($a)));
    }

    public function inside(float $lat, float $lon, float $centerLat, float $centerLon, float $radiusM): bool
    {
        return $this->distanceMeters($lat, $lon, $centerLat, $centerLon) <= $radiusM;
    }

    /**
     * @param  array<int, array{lat:float, lon:float}>  $stops  keyed by id
     * @return array<int> urutan id terdekat dari depot
     */
    public function optimizeSequence(float $depotLat, float $depotLon, array $stops): array
    {
        $order = [];
        $lat = $depotLat;
        $lon = $depotLon;
        while ($stops) {
            $best = null;
            $bestDist = INF;
            foreach ($stops as $id => $s) {
                $d = $this->distanceMeters($lat, $lon, $s['lat'], $s['lon']);
                if ($d < $bestDist) {
                    $bestDist = $d;
                    $best = $id;
                }
            }
            $order[] = $best;
            $lat = $stops[$best]['lat'];
            $lon = $stops[$best]['lon'];
            unset($stops[$best]);
        }

        return $order;
    }
}
