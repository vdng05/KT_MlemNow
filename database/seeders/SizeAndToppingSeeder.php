<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Food;
use App\Models\Size;
use App\Models\Topping;

class SizeAndToppingSeeder extends Seeder
{
    public function run()
    {
        // Xóa dữ liệu cũ để tránh trùng lặp khi chạy lại lệnh seed
        Size::query()->delete();
        Topping::query()->delete();

        $foods = Food::with('category')->get();

        foreach ($foods as $food) {
            $categoryName = $food->category->name ?? '';

            if ($categoryName === 'Pizza') {
                Size::insert([
                    ['food_id' => $food->id, 'name' => 'Size S (18cm)', 'extra_price' => 0],
                    ['food_id' => $food->id, 'name' => 'Size M (24cm)', 'extra_price' => 40000],
                    ['food_id' => $food->id, 'name' => 'Size L (30cm)', 'extra_price' => 80000],
                ]);
                Topping::insert([
                    ['food_id' => $food->id, 'name' => 'Thêm Phô Mai Mozzarella', 'extra_price' => 25000],
                    ['food_id' => $food->id, 'name' => 'Đế viền phô mai', 'extra_price' => 39000],
                    ['food_id' => $food->id, 'name' => 'Thêm Xúc Xích Pepperoni', 'extra_price' => 20000],
                ]);
            } elseif ($categoryName === 'Burger') {
                Size::insert([
                    ['food_id' => $food->id, 'name' => 'Cỡ Vừa (Tiêu chuẩn)', 'extra_price' => 0],
                    ['food_id' => $food->id, 'name' => 'Cỡ Lớn (Thêm thịt)', 'extra_price' => 35000],
                ]);
                Topping::insert([
                    ['food_id' => $food->id, 'name' => 'Thêm Phô Mai Cheddar', 'extra_price' => 15000],
                    ['food_id' => $food->id, 'name' => 'Thêm Trứng Ốp La', 'extra_price' => 10000],
                    ['food_id' => $food->id, 'name' => 'Thêm Bacon (Thịt xông khói)', 'extra_price' => 20000],
                ]);
            } elseif ($categoryName === 'Mì cay') {
                Size::insert([
                    ['food_id' => $food->id, 'name' => 'Cấp độ 0 (Không cay)', 'extra_price' => 0],
                    ['food_id' => $food->id, 'name' => 'Cấp độ 1-3 (Cay vừa)', 'extra_price' => 0],
                    ['food_id' => $food->id, 'name' => 'Cấp độ 4-7 (Siêu cay)', 'extra_price' => 0],
                ]);
                Topping::insert([
                    ['food_id' => $food->id, 'name' => 'Thêm Bò Mỹ', 'extra_price' => 25000],
                    ['food_id' => $food->id, 'name' => 'Thêm Xúc Xích', 'extra_price' => 15000],
                    ['food_id' => $food->id, 'name' => 'Thêm Nấm Kim Châm', 'extra_price' => 10000],
                    ['food_id' => $food->id, 'name' => 'Thêm Tôm Sú', 'extra_price' => 20000],
                ]);
            } elseif ($categoryName === 'Cơm') {
                Size::insert([
                    ['food_id' => $food->id, 'name' => 'Phần Tiêu Chuẩn', 'extra_price' => 0],
                    ['food_id' => $food->id, 'name' => 'Phần Lớn (Thêm cơm)', 'extra_price' => 10000],
                ]);
                Topping::insert([
                    ['food_id' => $food->id, 'name' => 'Thêm Trứng Ốp La', 'extra_price' => 8000],
                    ['food_id' => $food->id, 'name' => 'Thêm Lạp Xưởng', 'extra_price' => 12000],
                    ['food_id' => $food->id, 'name' => 'Thêm Canh Rong Biển', 'extra_price' => 15000],
                    ['food_id' => $food->id, 'name' => 'Thêm Kim Chi', 'extra_price' => 10000],
                ]);
            } elseif ($categoryName === 'Gà rán') {
                Size::insert([
                    ['food_id' => $food->id, 'name' => '1 Miếng', 'extra_price' => 0],
                    ['food_id' => $food->id, 'name' => 'Combo 2 Miếng + Khoai', 'extra_price' => 45000],
                    ['food_id' => $food->id, 'name' => 'Combo 3 Miếng', 'extra_price' => 60000],
                ]);
                Topping::insert([
                    ['food_id' => $food->id, 'name' => 'Thêm Sốt Phô Mai', 'extra_price' => 10000],
                    ['food_id' => $food->id, 'name' => 'Thêm Sốt Cay Hàn Quốc', 'extra_price' => 8000],
                ]);
            } elseif ($categoryName === 'Đồ uống') {
                Size::insert([
                    ['food_id' => $food->id, 'name' => 'Size M (500ml)', 'extra_price' => 0],
                    ['food_id' => $food->id, 'name' => 'Size L (700ml)', 'extra_price' => 10000],
                ]);
                Topping::insert([
                    ['food_id' => $food->id, 'name' => 'Thêm Trân Châu Đen', 'extra_price' => 8000],
                    ['food_id' => $food->id, 'name' => 'Thêm Trân Châu Trắng', 'extra_price' => 10000],
                    ['food_id' => $food->id, 'name' => 'Thêm Kem Cheese', 'extra_price' => 15000],
                    ['food_id' => $food->id, 'name' => 'Thêm Thạch Nha Đam', 'extra_price' => 8000],
                ]);
            }
        }
    }
}