<?php

namespace App\Adapters\Map;

interface MapDistanceInterface
{
    /**
     * Tính toán khoảng cách giữa 2 địa chỉ
     * @return float|null Khoảng cách tính bằng Km, null nếu không thể xác định
     */
    public function getDistanceInKm($originAddress, $destinationAddress): ?float;
}