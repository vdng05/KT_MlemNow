<?php

namespace App\Factories\Voucher;

interface DiscountProcessorInterface
{
    /**
     * Tính toán số tiền được giảm
     * @param float $subTotal Tổng tiền tạm tính của giỏ hàng
     * @param object $voucher Đối tượng voucher lấy từ Database
     * @return float Số tiền được giảm
     */
    public function calculateDiscount($subTotal, $voucher);
}