<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Voucher;
use Carbon\Carbon;

class VoucherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $vouchers = [
            [
                'code' => 'MLEMNOW20',
                'type' => 'amount', // Giảm tiền mặt cố định
                'discount_value' => 20000, // Giảm thẳng 20k
                'max_discount' => null,
                'quantity' => 50,
                'min_order_value' => 50000, // Đơn từ 50k mới được áp dụng
                'expires_at' => Carbon::now()->addDays(30),
            ],
            [
                'code' => 'GIAM10K',
                'type' => 'amount',
                'discount_value' => 10000,
                'max_discount' => null,
                'quantity' => 100,
                'min_order_value' => 0,
                'expires_at' => Carbon::now()->addDays(15),
            ],
            [
                'code' => 'CHANSAN',
                'type' => 'percent', // Giảm theo phần trăm
                'discount_value' => 15, // Giảm 15%
                'max_discount' => 30000, // Giảm tối đa 30k
                'quantity' => 30,
                'min_order_value' => 100000,
                'expires_at' => Carbon::now()->addDays(7),
            ],
            [
                'code' => 'FREESHIP',
                'type' => 'percent',
                'discount_value' => 100, // Giảm 100% (Ví dụ mô phỏng freeship qua phần trăm)
                'max_discount' => 20000, // Giảm tối đa bằng tiền ship (20k)
                'quantity' => 200,
                'min_order_value' => 120000,
                'expires_at' => Carbon::now()->addDays(45),
            ],
        ];

        foreach ($vouchers ?? [] as $voucher) {
            Voucher::create($voucher);
        }
    }
}