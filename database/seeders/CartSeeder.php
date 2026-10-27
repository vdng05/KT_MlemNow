<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\User;
use Carbon\Carbon;

class CartSeeder extends Seeder {
    public function run(): void {
        Cart::query()->delete();
        
        $userDang = User::where('email', 'dangvh@gmail.com')->first();
        $userDung = User::where('email', 'dungvt@gmail.com')->first();

        // Giỏ hàng 1: Đang hoạt động bình thường
        if ($userDang) {
            $cart1 = Cart::create([
                'session_id' => 'sess_' . md5(time() . 'dang'),
                'user_id' => $userDang->id,
                'updated_at' => Carbon::now()
            ]);

            CartItem::insert([
                [
                    'cart_id' => $cart1->id, 'food_id' => 1, 
                    'size_name' => 'Size L', 'toppings' => 'Phô mai', 
                    'quantity' => 2, 'price' => 120000, 'note' => 'Ít cay',
                    'created_at' => Carbon::now(), 'updated_at' => Carbon::now()
                ]
            ]);
        }

        // Giỏ hàng 2: Giỏ hàng "Rác" (bỏ quên 2 ngày trước để test Command Pattern)
        if ($userDung) {
            $cart2 = Cart::create([
                'session_id' => 'sess_' . md5(time() . 'dung'),
                'user_id' => $userDung->id,
                'updated_at' => Carbon::now()->subDays(2) // Đã quá 24h
            ]);

            CartItem::insert([
                [
                    'cart_id' => $cart2->id, 'food_id' => 2, 
                    'size_name' => 'Size M', 'toppings' => null, 
                    'quantity' => 1, 'price' => 50000, 'note' => '',
                    'created_at' => Carbon::now()->subDays(2), 'updated_at' => Carbon::now()->subDays(2)
                ]
            ]);
        }
    }
}