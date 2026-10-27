<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserAddressSeeder extends Seeder
{
    public function run()
    {
        // Xóa dữ liệu cũ trước khi thêm mới
        DB::table('user_addresses')->delete();

        $data = [
            ['email' => 'dungvt@gmail.com', 'address' => 'Tòa nhà Lotte, 54 Liễu Giai, Ba Đình, Hà Nội'],
            ['email' => 'dangvh@gmail.com', 'address' => 'Ký túc xá Đại học Thủy Lợi, 175 Tây Sơn, Đống Đa, Hà Nội'],
            ['email' => 'dangvh@gmail.com', 'address' => '12 Chùa Bộc, Quang Trung, Đống Đa, Hà Nội'],
            ['email' => 'daivb@gmail.com', 'address' => '89 Phố Vọng, Đồng Tâm, Hai Bà Trưng, Hà Nội'],
        ];

        foreach ($data as $item) {
            $user = User::where('email', $item['email'])->first();
            if ($user) {
                DB::table('user_addresses')->insert([
                    'user_id' => $user->id,
                    'address' => $item['address'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}