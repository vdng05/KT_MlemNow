<?php

namespace App\Factories\Voucher;

use App\Models\Voucher;
use Exception;

class VoucherFactory
{
    public static function make($typeOrVoucher): DiscountProcessorInterface
    {
        if ($typeOrVoucher instanceof Voucher) {
            if ($typeOrVoucher->isBirthday()) {
                return new BirthdayDiscountProcessor();
            }
            $type = $typeOrVoucher->type;
        } else {
            $type = (string) $typeOrVoucher;
        }

        return match($type) {
            'percent'  => new PercentDiscountProcessor(),
            'amount'   => new AmountDiscountProcessor(),
            'birthday' => new BirthdayDiscountProcessor(),
            default    => throw new Exception("Loại voucher không hợp lệ: " . $type),
        };
    }
}