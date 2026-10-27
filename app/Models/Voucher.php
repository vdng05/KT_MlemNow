<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_birthday' => 'boolean',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'discount_value' => 'float',
        'max_discount' => 'float',
        'min_order_value' => 'float',
        'quantity' => 'integer',
    ];

    /**
     * Kiểm tra xem đây có phải là voucher ưu đãi sinh nhật hay không.
     */
    public function isBirthday(): bool
    {
        return (bool) $this->is_birthday 
            || $this->type === 'birthday' 
            || strtoupper($this->code) === 'SINHNHAT';
    }

    /**
     * Kiểm tra thời điểm hiện tại có nằm trong vòng 7 ngày liên tiếp kể từ ngày sinh nhật hay không.
     * Biên dưới: Ngày trước sinh nhật => Không hợp lệ.
     * Biên chuẩn: Ngày sinh nhật (ngày 1) đến ngày thứ 7 kể từ ngày sinh nhật => Hợp lệ.
     * Biên trên: Ngày thứ 8 kể từ ngày sinh nhật => Không hợp lệ.
     */
    public static function isWithinBirthdayWindow(?string $birthDateString, ?Carbon $now = null): bool
    {
        if (empty($birthDateString)) {
            return false;
        }

        $now = $now ? $now->copy() : Carbon::now();
        $birthDate = Carbon::parse($birthDateString);

        $currentYear = $now->year;
        $day = min($birthDate->day, 28);
        if ($birthDate->month !== 2 || $birthDate->day < 29 || Carbon::create($currentYear, 1, 1)->isLeapYear()) {
            $day = $birthDate->day;
        }

        // Sinh nhật trong năm hiện tại
        $birthdayThisYear = Carbon::create($currentYear, $birthDate->month, $day)->startOfDay();
        
        // 7 ngày liên tiếp tính cả ngày sinh nhật (ngày 1 đến ngày 7):
        // Ví dụ sinh nhật 15/10: từ 00:00:00 ngày 15/10 đến 23:59:59 ngày 21/10 (+6 ngày).
        $windowStart = $birthdayThisYear->copy()->startOfDay();
        $windowEnd = $birthdayThisYear->copy()->addDays(6)->endOfDay();

        if ($now->between($windowStart, $windowEnd)) {
            return true;
        }

        // Kiểm tra thêm trường hợp ngày sinh rơi vào cuối năm trước (ví dụ 30/12 và hiện tại là 02/01 năm mới)
        $birthdayLastYear = $birthdayThisYear->copy()->subYear();
        $windowStartLastYear = $birthdayLastYear->copy()->startOfDay();
        $windowEndLastYear = $birthdayLastYear->copy()->addDays(6)->endOfDay();

        return $now->between($windowStartLastYear, $windowEndLastYear);
    }
}
