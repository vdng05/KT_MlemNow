<?php

namespace App\Factories\Voucher;

use Exception;

class VoucherFactory
{
    public static function make($type)
    {
        return match($type) {
            'percent' => new PercentDiscountProcessor(),
            'amount'  => new AmountDiscountProcessor(),
            default   => throw new Exception("Loại voucher không hợp lệ: " . $type),
        };
    }
}