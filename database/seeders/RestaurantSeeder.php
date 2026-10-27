<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Restaurant;

class RestaurantSeeder extends Seeder
{
    public function run()
    {
        $restaurants = [
            [
                'name' => 'Pizza House', 
                'rating' => 4.8, 
                'distance' => 1.2, 
                'image' => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?q=80&w=1200' // Ảnh không gian nhà hàng Ý
            ],
            [
                'name' => 'Burger Town', 
                'rating' => 4.7, 
                'distance' => 0.9, 
                'image' => 'https://images.unsplash.com/photo-1552566626-52f8b828add9?q=80&w=1200' // Ảnh quán ăn nhanh hiện đại
            ],
            [
                'name' => 'Chicken King', 
                'rating' => 4.9, 
                'distance' => 2.0, 
                'image' => 'https://images.unsplash.com/photo-1466978913421-dad2ebd01d17?q=80&w=1200' // Quán gà rán ánh đèn neon
            ],
            [
                'name' => 'Korean Food', 
                'rating' => 4.6, 
                'distance' => 1.5, 
                'image' => 'https://images.unsplash.com/photo-1514933651103-005eec06c04b?q=80&w=1200' // Quán ăn phong cách Hàn Quốc
            ],
            [
                'name' => 'Cơm Ngon 24h', 
                'rating' => 4.9, 
                'distance' => 2.0, 
                'image' => 'https://images.unsplash.com/photo-1559339352-11d035aa65de?q=80&w=1200' // Quán ăn gia đình ấm cúng
            ],
            [
                'name' => 'Cơm Tấm Sài Gòn', 
                'rating' => 4.8, 
                'distance' => 2.1, 
                'image' => 'https://images.unsplash.com/photo-1554679665-f5537f187268?q=80&w=1200' // Quán ăn đường phố nhộn nhịp
            ],
            [
                'name' => 'Beef Rice House', 
                'rating' => 4.9, 
                'distance' => 2.0, 
                'image' => 'https://images.unsplash.com/photo-1544148103-0773bf10d330?q=80&w=1200' // Nhà hàng Steakhouse
            ],
            [
                'name' => 'Mlem Coffee House', 
                'rating' => 4.9, 
                'distance' => 1.7, 
                'image' => 'https://images.unsplash.com/photo-1554118811-1e0d58224f24?q=80&w=1200' // Không gian quán Cafe chill
            ],
            [
                'name' => 'Healthy Meal', 
                'rating' => 4.8, 
                'distance' => 1.3, 
                'image' => 'https://images.unsplash.com/photo-1493770348161-369560ae357d?q=80&w=1200' // Không gian xanh, đồ ăn healthy
            ],
            [
                'name' => 'Seafood House', 
                'rating' => 4.7, 
                'distance' => 2.5, 
                'image' => 'https://images.unsplash.com/photo-1534080564583-6be75777b70a?q=80&w=1200' // Nhà hàng hải sản ven biển
            ],
        ];

        foreach ($restaurants as $res) {
            Restaurant::updateOrCreate(
                ['name' => $res['name']], // Tìm xem quán đã tồn tại chưa
                [
                    'rating' => $res['rating'],
                    'distance' => $res['distance'],
                    'image' => $res['image'] // Cập nhật hình ảnh mới
                ]
            );
        }
    }
}