<?php

namespace App\Factories\Voucher;

class BirthdayDiscountProcessor implements DiscountProcessorInterface
{
    public function calculateDiscount($subTotal, $voucher)
    {
        // Giảm ưu đãi 50.000đ cho voucher sinh nhật theo đặc tả
        $discount = ($voucher && $voucher->discount_value > 0) ? $voucher->discount_value : 50000;

        return min($subTotal, $discount);
    }
}

