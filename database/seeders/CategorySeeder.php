<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            [
                'name' => 'Pizza', 
                'image' => 'https://images.unsplash.com/photo-1513104890138-7c749659a591?q=80&w=1200'
            ],
            [
                'name' => 'Burger', 
                'image' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?q=80&w=1200'
            ],
            [
                'name' => 'Gà rán', 
                'image' => 'https://images.unsplash.com/photo-1626082927389-6cd097cdc6ec?q=80&w=1200'
            ],
            [
                'name' => 'Đồ uống', 
                'image' => 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?q=80&w=1200'
            ],
            [
                'name' => 'Mì cay', 
                'image' => 'https://images.unsplash.com/photo-1559847844-5315695dadae?q=80&w=1200'
            ],
            [
                'name' => 'Cơm', 
                'image' => 'https://images.unsplash.com/photo-1512058564366-18510be2db19?q=80&w=1200'
            ]
        ];

        foreach ($categories as $cat) {
            // Dùng updateOrCreate để cập nhật ảnh mới mà không bị lỗi trùng lặp
            Category::updateOrCreate(
                ['name' => $cat['name']],
                ['image' => $cat['image']]
            );
        }
    }
}