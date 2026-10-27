<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Xóa dữ liệu cũ tránh trùng lặp khi làm mới database
        User::query()->delete();

        $members = [
            [
                'name' => 'Vương Tiến Dũng',
                'email' => 'dungvt@gmail.com',
                'phone' => '0937243758',
                'address' => '26 Đông Tác, Kim Liên, Đống Đa, Hà Nội',
                'password' => Hash::make('12345678'),
                'reward_points' => 18,
            ],
            [
                'name' => 'Vũ Hải Đăng',
                'email' => 'dangvh@gmail.com',
                'phone' => '0914054210',
                'address' => '105 ngõ 12 Phan Đình Giót, Phương Liệt, Thanh Xuân, Hà Nội',
                'password' => Hash::make('12345678'),
                'reward_points' => 200,
            ],
            [
                'name' => 'Đinh Thị Hoa',
                'email' => 'hoadt@gmail.com',
                'phone' => '0912345678',
                'address' => 'Phòng 402 Tòa A1, Đại học Thủy Lợi, Đống Đa, Hà Nội',
                'password' => Hash::make('12345678'),
                'reward_points' => 13,
            ],
            [
                'name' => 'Vũ Bá Đài',
                'email' => 'daivb@gmail.com',
                'phone' => '0934567890',
                'address' => '1 Bùi Xương Trạch, Phương Liệt, Thanh Xuân, Hà Nội',
                'password' => Hash::make('12345678'),
                'reward_points' => 36,
            ],
        ];

        foreach ($members as $member) {
            User::create($member);
        }
    }
}