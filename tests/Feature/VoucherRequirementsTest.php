<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Voucher;
use Carbon\Carbon;
use Tests\TestCase;

class VoucherRequirementsTest extends TestCase
{
    public function test_voucher_code_format_and_existence(): void
    {
        $user = User::where('email', 'hoadt@gmail.com')->first() ?: User::factory()->create();

        // 1. Mã hợp lệ tồn tại
        $res1 = $this->actingAs($user)->postJson(route('checkout.check_voucher'), [
            'code' => 'MLEMNOW20',
        ]);
        $res1->assertJson(['success' => true]);

        // 2. Mã chứa ký tự đặc biệt
        $res2 = $this->actingAs($user)->postJson(route('checkout.check_voucher'), [
            'code' => 'GIAM10#%',
        ]);
        $res2->assertJson(['success' => false, 'message' => 'Mã voucher không tồn tại.']);

        // 3. Mã không tồn tại
        $res3 = $this->actingAs($user)->postJson(route('checkout.check_voucher'), [
            'code' => 'MUANGAY',
        ]);
        $res3->assertJson(['success' => false, 'message' => 'Mã voucher không tồn tại.']);
    }

    public function test_birthday_voucher_conditions(): void
    {
        $today = Carbon::now();

        // Khách sinh nhật hôm nay
        $birthdayUser = User::where('email', 'hoadt@gmail.com')->first();
        if (!$birthdayUser) {
            $birthdayUser = User::factory()->create(['birth_date' => $today->toDateString()]);
        }

        // Khách sinh nhật cách xa 6 tháng
        $otherUser = User::where('email', 'dungvt@gmail.com')->first();
        if (!$otherUser) {
            $otherUser = User::factory()->create(['birth_date' => $today->copy()->subMonths(6)->toDateString()]);
        }

        session()->put('cart', [['price' => 120000, 'quantity' => 1]]);

        // Khách sinh nhật hôm nay áp dụng thành công
        $resValid = $this->actingAs($birthdayUser)->postJson(route('checkout.check_voucher'), [
            'code' => 'SINHNHAT',
        ]);
        $resValid->assertJson([
            'success' => true,
            'discount_amount' => 50000,
        ]);

        // Khách không trong kỳ sinh nhật bị từ chối
        $resInvalid = $this->actingAs($otherUser)->postJson(route('checkout.check_voucher'), [
            'code' => 'SINHNHAT',
        ]);
        $resInvalid->assertJson([
            'success' => false,
            'message' => 'Bạn chưa đủ điều kiện sử dụng voucher này',
        ]);
    }
}

