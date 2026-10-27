<?php

namespace App\Adapters\Map;

use Illuminate\Support\Facades\Http;

class OpenStreetMapAdapter implements MapDistanceInterface
{
    public function getDistanceInKm($originAddress, $destinationAddress): ?float
    {
        try {
            // 1. Gọi API để lấy tọa độ
            $origin = $this->geocode($originAddress);
            $dest = $this->geocode($destinationAddress);

            if (!$origin || !$dest) {
                return null;
            }

            // 2. Phiên dịch tọa độ thành khoảng cách Km thực tế
            return $this->calculateHaversine($origin['lat'], $origin['lon'], $dest['lat'], $dest['lon']);
            
        } catch (\Throwable $e) {
            return null;
        }
    }

    // Hàm gọi API của OpenStreetMap (Nominatim)
    private function geocode($address)
    {
        $response = Http::timeout(5)->withHeaders([
            'User-Agent' => 'MlemNow-Student-Project'
        ])->get('https://nominatim.openstreetmap.org/search', [
            'q' => $address,
            'format' => 'json',
            'limit' => 1
        ]);

        if (!$response->successful()) {
            return null;
        }

        $data = $response->json();
        if (is_array($data) && count($data) > 0) {
            return [
                'lat' => (float) $data[0]['lat'],
                'lon' => (float) $data[0]['lon']
            ];
        }
        return null;
    }

    // Công thức tính khoảng cách đường chim bay giữa 2 tọa độ
    private function calculateHaversine($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // Bán kính trái đất (km)
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        
        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        
        return round($earthRadius * $c, 2);
    }
}