<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;

class OrderSeeder extends Seeder
{
    public function run()
    {
        // Xóa sạch dữ liệu cũ để làm mới cấu trúc dòng tiền
        Order::query()->delete();

        $userDung = User::where('email', 'dungvt@gmail.com')->first();
        $userDang = User::where('email', 'dangvh@gmail.com')->first();

        // 1. Sinh đơn hàng mẫu cho tài khoản Vương Tiến Dũng
        if ($userDung) {
            $order1 = Order::create([
                'order_code' => 'MN30041975',
                'user_id' => $userDung->id,
                'customer_name' => $userDung->name,
                'phone' => $userDung->phone,
                'delivery_address' => $userDung->address ?? '26 Đông Tác, Kim Liên, Đống Đa, Hà Nội',
                'payment_method' => 'COD',
                'subtotal' => 220000,
                'delivery_fee' => 20000,
                'discount_applied' => 17000,
                'total_amount' => 223000,
                'status' => 'delivering',
                'created_at' => now()->subDays(1),
            ]);

            OrderItem::insert([
                ['order_id' => $order1->id, 'food_name' => 'Pizza hải sản', 'size_name' => 'Size L', 'quantity' => 1, 'price' => 120000],
                ['order_id' => $order1->id, 'food_name' => 'Mì ý sốt cay', 'size_name' => 'Size nhỏ', 'quantity' => 2, 'price' => 50000],
            ]);
        }

        // 2. Sinh đơn hàng mẫu cho tài khoản Vũ Hải Đăng
        if ($userDang) {
            $order2 = Order::create([
                'order_code' => 'MN23142999',
                'user_id' => $userDang->id,
                'customer_name' => $userDang->name,
                'phone' => $userDang->phone,
                'delivery_address' => $userDang->address ?? '105 ngõ 12 Phan Đình Giót, Phương Liệt, Thanh Xuân, Hà Nội',
                'payment_method' => 'COD',
                'subtotal' => 150000,
                'delivery_fee' => 20000,
                'discount_applied' => 0,
                'total_amount' => 170000,
                'status' => 'completed',
                'created_at' => now(),
            ]);

            OrderItem::insert([
                ['order_id' => $order2->id, 'food_name' => 'Mì cay thập cẩm', 'size_name' => 'Size L', 'quantity' => 1, 'price' => 120000],
                ['order_id' => $order2->id, 'food_name' => 'Trà Trái Cây Nhiệt Đới', 'size_name' => 'Size M', 'quantity' => 1, 'price' => 30000],
            ]);
        }
    }
}