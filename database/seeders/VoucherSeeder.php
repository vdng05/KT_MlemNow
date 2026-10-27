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
        // Xóa sạch dữ liệu voucher cũ để nạp mới dữ liệu chuẩn đặc tả
        Voucher::query()->delete();

        $vouchers = [
            // 1. Voucher chuẩn theo đặc tả: Giảm 10% cho đơn từ 100.000đ
            [
                'code' => 'MLEMNOW20',
                'type' => 'percent',
                'is_birthday' => false,
                'discount_value' => 10, // 10%
                'max_discount' => null,
                'quantity' => 100,
                'min_order_value' => 100000,
                'starts_at' => Carbon::now()->subDays(7),
                'expires_at' => Carbon::now()->addDays(30),
            ],

            // 2. Voucher sinh nhật: Giảm 50.000đ cho đơn từ 100.000đ trong vòng 7 ngày kể từ sinh nhật
            [
                'code' => 'SINHNHAT',
                'type' => 'birthday',
                'is_birthday' => true,
                'discount_value' => 50000, // 50.000đ
                'max_discount' => null,
                'quantity' => 100,
                'min_order_value' => 100000,
                'starts_at' => Carbon::now()->subDays(30),
                'expires_at' => Carbon::now()->addDays(365),
            ],

            // 3. Voucher chưa đến thời gian áp dụng (dùng cho ca kiểm thử 3.5.10.3 STT 2)
            [
                'code' => 'VOUCHERSOON',
                'type' => 'percent',
                'is_birthday' => false,
                'discount_value' => 10,
                'max_discount' => null,
                'quantity' => 50,
                'min_order_value' => 100000,
                'starts_at' => Carbon::now()->addDays(5), // Chưa đến thời gian
                'expires_at' => Carbon::now()->addDays(30),
            ],

            // 4. Voucher đã hết thời gian áp dụng (dùng cho ca kiểm thử 3.5.10.3 STT 3)
            [
                'code' => 'VOUCHERHETHAN',
                'type' => 'percent',
                'is_birthday' => false,
                'discount_value' => 10,
                'max_discount' => null,
                'quantity' => 50,
                'min_order_value' => 100000,
                'starts_at' => Carbon::now()->subDays(30),
                'expires_at' => Carbon::now()->subDays(2), // Đã hết hạn
            ],

            // 5. Voucher hết lượt sử dụng (quantity = 0)
            [
                'code' => 'HETLUOTDUNG',
                'type' => 'percent',
                'is_birthday' => false,
                'discount_value' => 10,
                'max_discount' => null,
                'quantity' => 0, // Hết lượt
                'min_order_value' => 100000,
                'starts_at' => Carbon::now()->subDays(10),
                'expires_at' => Carbon::now()->addDays(30),
            ],

            // 6. Voucher giảm tiền mặt cố định 10.000đ
            [
                'code' => 'GIAM10K',
                'type' => 'amount',
                'is_birthday' => false,
                'discount_value' => 10000,
                'max_discount' => null,
                'quantity' => 100,
                'min_order_value' => 100000,
                'starts_at' => Carbon::now()->subDays(15),
                'expires_at' => Carbon::now()->addDays(30),
            ],

            // 7. Voucher giảm phần trăm có giới hạn tối đa
            [
                'code' => 'CHANSAN',
                'type' => 'percent',
                'is_birthday' => false,
                'discount_value' => 15,
                'max_discount' => 30000,
                'quantity' => 30,
                'min_order_value' => 100000,
                'starts_at' => Carbon::now()->subDays(5),
                'expires_at' => Carbon::now()->addDays(15),
            ],

            // 8. Voucher freeship giảm tối đa 20.000đ tiền ship
            [
                'code' => 'FREESHIP',
                'type' => 'amount',
                'is_birthday' => false,
                'discount_value' => 20000,
                'max_discount' => 20000,
                'quantity' => 200,
                'min_order_value' => 100000,
                'starts_at' => Carbon::now()->subDays(10),
                'expires_at' => Carbon::now()->addDays(60),
            ],
        ];

        foreach ($vouchers as $voucher) {
            Voucher::create($voucher);
        }
    }
}