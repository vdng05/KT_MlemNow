<?php

namespace App\Factories\Voucher;

class PercentDiscountProcessor implements DiscountProcessorInterface
{
    public function calculateDiscount($subTotal, $voucher)
    {
        // Tính tiền giảm dựa trên phần trăm (ví dụ: 10%)
        $discount = ($subTotal * $voucher->discount_value) / 100;

        // Nếu voucher có giới hạn số tiền giảm tối đa (ví dụ: Giảm 10% nhưng tối đa 30k)
        if (isset($voucher->max_discount) && $voucher->max_discount > 0) {
            if ($discount > $voucher->max_discount) {
                $discount = $voucher->max_discount;
            }
        }

        return $discount;
    }
}