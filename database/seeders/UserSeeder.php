<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Xóa dữ liệu cũ tránh trùng lặp khi làm mới database
        User::query()->delete();

        $today = Carbon::now();

        $members = [
            [
                'name' => 'Vương Tiến Dũng',
                'email' => 'dungvt@gmail.com',
                'phone' => '0937243758',
                'address' => '26 Đông Tác, Kim Liên, Đống Đa, Hà Nội',
                'password' => Hash::make('12345678'),
                'reward_points' => 18,
                // Ngày sinh cách xa hiện tại 6 tháng => Không nằm trong 7 ngày sinh nhật
                'birth_date' => $today->copy()->subMonths(6)->setYear(2005)->toDateString(),
            ],
            [
                'name' => 'Vũ Hải Đăng',
                'email' => 'dangvh@gmail.com',
                'phone' => '0914054210',
                'address' => '105 ngõ 12 Phan Đình Giót, Phương Liệt, Thanh Xuân, Hà Nội',
                'password' => Hash::make('12345678'),
                'reward_points' => 200,
                // Chưa nhập ngày sinh => Không đủ điều kiện dùng voucher sinh nhật
                'birth_date' => null,
            ],
            [
                'name' => 'Đinh Thị Hoa',
                'email' => 'hoadt@gmail.com',
                'phone' => '0912345678',
                'address' => 'Phòng 402 Tòa A1, Đại học Thủy Lợi, Đống Đa, Hà Nội',
                'password' => Hash::make('12345678'),
                'reward_points' => 13,
                // Sinh nhật đúng ngày hôm nay => Hợp lệ dùng voucher sinh nhật SINHNHAT
                'birth_date' => $today->copy()->setYear(2005)->toDateString(),
            ],
            [
                'name' => 'Vũ Bá Đài',
                'email' => 'daivb@gmail.com',
                'phone' => '0934567890',
                'address' => '1 Bùi Xương Trạch, Phương Liệt, Thanh Xuân, Hà Nội',
                'password' => Hash::make('12345678'),
                'reward_points' => 36,
                // Sinh nhật cách đây 3 ngày => Vẫn nằm trong 7 ngày liên tiếp kể từ sinh nhật (Hợp lệ)
                'birth_date' => $today->copy()->subDays(3)->setYear(2004)->toDateString(),
            ],
        ];

        foreach ($members as $member) {
            User::create($member);
        }
    }
}