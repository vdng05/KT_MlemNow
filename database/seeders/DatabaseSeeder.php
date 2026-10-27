<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            RestaurantSeeder::class,
            FoodSeeder::class,
            SizeAndToppingSeeder::class,
            VoucherSeeder::class,
            OrderSeeder::class,
            UserAddressSeeder::class,
            CartSeeder::class,
        ]);
    }
}