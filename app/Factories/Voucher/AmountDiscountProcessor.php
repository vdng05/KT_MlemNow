<?php

namespace App\Factories\Voucher;

class AmountDiscountProcessor implements DiscountProcessorInterface
{
    public function calculateDiscount($subTotal, $voucher)
    {
        // Đảm bảo số tiền giảm không vượt quá tổng tiền của đơn hàng
        return min($subTotal, $voucher->discount_value);
    }
}