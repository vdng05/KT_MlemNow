<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Food;
use App\Models\Category;
use App\Models\Restaurant;

class FoodSeeder extends Seeder
{
    public function run()
    {
        // Lấy ID danh mục & Quán ăn
        $catPizza = Category::where('name', 'Pizza')->first()->id;
        $catBurger = Category::where('name', 'Burger')->first()->id;
        $catGa = Category::where('name', 'Gà rán')->first()->id;
        $catCom = Category::where('name', 'Cơm')->first()->id;
        $catDoUong = Category::where('name', 'Đồ uống')->first()->id;
        $catMiCay = Category::where('name', 'Mì cay')->first()->id;

        $resPizza = Restaurant::where('name', 'Pizza House')->first()->id;
        $resBurger = Restaurant::where('name', 'Burger Town')->first()->id;
        $resChicken = Restaurant::where('name', 'Chicken King')->first()->id;
        $resKorean = Restaurant::where('name', 'Korean Food')->first()->id;
        $resComTam = Restaurant::where('name', 'Cơm Tấm Sài Gòn')->first()->id;
        $resComNgon = Restaurant::where('name', 'Cơm Ngon 24h')->first()->id;
        $resBeef = Restaurant::where('name', 'Beef Rice House')->first()->id;
        $resCoffee = Restaurant::where('name', 'Mlem Coffee House')->first()->id;

        $foods = [
            // ================= MÌ CAY =================
            [
                'category_id' => $catMiCay,
                'restaurant_id' => $resKorean,
                'name' => 'Mì Cay Bò',
                'description' => 'Mì cay Hàn Quốc với thịt bò Mỹ (Cấp 0-7). Nước dùng đậm đà, chua cay kích thích vị giác.',
                'nutrition_info' => 'Năng lượng: 520 kcal | Protein: 25g | Chất béo: 18g | Carbs: 65g | Natri: 1200mg. Một khẩu phần giàu đạm từ thịt bò, thích hợp bổ sung năng lượng.',
                'ingredients' => 'Mì sợi to Hàn Quốc, 100g ba chỉ bò Mỹ thái mỏng, kim chi cải thảo lên men, nấm kim châm, súp lơ xanh, ớt bột Gochugaru, nước hầm xương 24h.',
                'base_price' => 55000,
                'image' => 'https://images.unsplash.com/photo-1559847844-5315695dadae?q=80&w=1200'
            ],
            [
                'category_id' => $catMiCay,
                'restaurant_id' => $resKorean,
                'name' => 'Mì Cay Thập Cẩm',
                'description' => 'Mì cay đẫm topping với sự kết hợp hoàn hảo giữa hải sản tươi và thịt bò béo ngậy.',
                'nutrition_info' => 'Năng lượng: 650 kcal | Protein: 35g | Chất béo: 22g | Carbs: 70g. Đầy đủ dưỡng chất với đa dạng nguồn đạm từ hải sản và thịt đỏ.',
                'ingredients' => 'Mì Koreno sợi dai, tôm sú, mực ống, ba chỉ bò Mỹ, cá viên, xúc xích, nấm kim châm, bắp cải tím, ớt sừng, nước dùng tomyum cay nồng.',
                'base_price' => 75000,
                'image' => 'https://images.unsplash.com/photo-1585032226651-759b368d7246?q=80&w=1200'
            ],

            // ================= PIZZA =================
            [
                'category_id' => $catPizza,
                'restaurant_id' => $resPizza,
                'name' => 'Pizza Hải Sản',
                'description' => 'Pizza hải sản tươi ngon với lớp phô mai Mozzarella kéo sợi béo ngậy, nướng củi thơm lừng.',
                'nutrition_info' => 'Năng lượng: 280 kcal/lát | Protein: 14g | Chất béo: 12g | Carbs: 30g. Cung cấp canxi từ phô mai và hải sản.',
                'ingredients' => 'Bột mì nguyên cám ủ men tự nhiên, tôm biển, mực xắt khoanh, thanh cua Nhật, phô mai Mozzarella Ý, sốt cà chua tươi, hành tây, ớt chuông, lá Oregano.',
                'base_price' => 120000,
                'sale_price' => 105000,
                'image' => 'https://images.unsplash.com/photo-1513104890138-7c749659a591?q=80&w=1200'
            ],
            [
                'category_id' => $catPizza,
                'restaurant_id' => $resPizza,
                'name' => 'Pizza Phô Mai',
                'description' => 'Chiếc bánh kinh điển dành cho tín đồ yêu thích vị béo ngậy của 4 loại phô mai thượng hạng.',
                'nutrition_info' => 'Năng lượng: 330 kcal/lát | Protein: 16g | Chất béo: 18g | Carbs: 25g. Rất giàu Canxi và chất béo tự nhiên.',
                'ingredients' => 'Đế bánh nướng tay, Phô mai Mozzarella, Phô mai Cheddar, Phô mai Parmesan, Phô mai xanh (Blue cheese), sốt kem sữa, dầu Olive extra virgin.',
                'base_price' => 110000,
                'sale_price' => 99000,
                'image' => 'https://images.unsplash.com/photo-1574071318508-1cdbab80d002?q=80&w=1200'
            ],

            // ================= BURGER =================
            [
                'category_id' => $catBurger,
                'restaurant_id' => $resBurger,
                'name' => 'Burger Bò',
                'description' => 'Burger bò nướng tảng chuẩn vị Mỹ, thịt mọng nước kẹp cùng rau củ tươi mát.',
                'nutrition_info' => 'Năng lượng: 450 kcal | Protein: 25g | Chất béo: 20g | Carbs: 42g. Cung cấp Sắt và Vitamin B12 từ thịt bò.',
                'ingredients' => 'Vỏ bánh mì Brioche rắc vừng, 150g thịt bò Úc xay nướng lửa hồng, xà lách mỡ, cà chua Đà Lạt, hành tây, tương cà, sốt Mayonnaise.',
                'base_price' => 80000,
                'image' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?q=80&w=1200'
            ],
            [
                'category_id' => $catBurger,
                'restaurant_id' => $resBurger,
                'name' => 'Burger Phô Mai',
                'description' => 'Phiên bản nâng cấp với lớp phô mai Cheddar tan chảy quyện cùng nước thịt bò nướng.',
                'nutrition_info' => 'Năng lượng: 520 kcal | Protein: 28g | Chất béo: 25g | Carbs: 45g.',
                'ingredients' => 'Bánh mì nướng bơ tỏi, bò bằm nướng, 2 lát phô mai Cheddar đút lò, dưa chuột muối chua, xà lách, sốt BBQ khói.',
                'base_price' => 89000,
                'sale_price' => 85000,
                'image' => 'https://images.unsplash.com/photo-1550547660-d9450f859349?q=80&w=1200'
            ],

            // ================= GÀ RÁN =================
            [
                'category_id' => $catGa,
                'restaurant_id' => $resChicken,
                'name' => 'Gà Rán Cay',
                'description' => 'Gà rán giòn rụm tẩm ướp gia vị cay nồng phong cách Hàn Quốc.',
                'nutrition_info' => 'Năng lượng: 350 kcal/miếng | Protein: 18g | Chất béo: 22g | Carbs: 15g.',
                'ingredients' => 'Thịt gà tươi sống 100% đạt chuẩn, bột chiên giòn bí truyền 11 loại thảo mộc, ớt bột Hàn Quốc Gochugaru, tỏi, gừng, dầu đậu nành nguyên chất.',
                'base_price' => 95000,
                'image' => 'https://images.unsplash.com/photo-1626082927389-6cd097cdc6ec?q=80&w=1200'
            ],

            // ================= CƠM =================
            [
                'category_id' => $catCom,
                'restaurant_id' => $resComTam,
                'name' => 'Cơm Sườn Nướng',
                'description' => 'Cơm tấm Sài Gòn truyền thống với sườn cốt lết nướng than hoa thơm lừng.',
                'nutrition_info' => 'Năng lượng: 650 kcal | Protein: 35g | Chất béo: 20g | Carbs: 80g. Bữa ăn no lâu, dồi dào năng lượng.',
                'ingredients' => 'Gạo tấm thơm dẻo, sườn cốt lết heo ướp mật ong sả, đồ chua (cà rốt, củ cải), mỡ hành tóp mỡ giòn, nước mắm kẹo độc quyền.',
                'base_price' => 70000,
                'image' => 'https://images.unsplash.com/photo-1604908176997-125f25cc6f3d?q=80&w=1200'
            ],

                // ================= MÌ CAY =================
            [
                'category_id' => $catMiCay,
                'restaurant_id' => $resKorean,
                'name' => 'Mì Cay Hải Sản',
                'description' => 'Mì cay với tôm, mực và nghêu tươi.',
                'nutrition_info' => 'Năng lượng: 580 kcal | Protein: 30g',
                'ingredients' => 'Mì Hàn Quốc, tôm, mực, nghêu, nấm, kim chi.',
                'base_price' => 69000,
                'image' => 'https://images.unsplash.com/photo-1612929633738-8fe44f7ec841?q=80&w=1200'
            ],
            [
                'category_id' => $catMiCay,
                'restaurant_id' => $resKorean,
                'name' => 'Mì Cay Kim Chi',
                'description' => 'Đậm vị kim chi truyền thống Hàn Quốc.',
                'nutrition_info' => 'Năng lượng: 500 kcal',
                'ingredients' => 'Mì, kim chi, thịt heo, nấm.',
                'base_price' => 59000,
                'image' => 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?q=80&w=1200'
            ],
            [
                'category_id' => $catMiCay,
                'restaurant_id' => $resKorean,
                'name' => 'Mì Cay Xúc Xích',
                'description' => 'Mì cay kết hợp xúc xích Đức và phô mai.',
                'nutrition_info' => 'Năng lượng: 620 kcal',
                'ingredients' => 'Mì, xúc xích Đức, phô mai, rau củ.',
                'base_price' => 65000,
                'image' => 'https://images.unsplash.com/photo-1617093727343-374698b1b08d?q=80&w=1200'
            ],

                // ================= PIZZA =================
            [
                'category_id' => $catPizza,
                'restaurant_id' => $resPizza,
                'name' => 'Pizza Pepperoni',
                'description' => 'Pizza truyền thống với xúc xích Pepperoni.',
                'nutrition_info' => 'Năng lượng: 310 kcal/lát',
                'ingredients' => 'Pepperoni, phô mai Mozzarella, sốt cà chua.',
                'base_price' => 125000,
                'sale_price' => 115000,
                'image' => 'https://images.unsplash.com/photo-1534308983496-4fabb1a015ee?q=80&w=1200'
            ],
            [
                'category_id' => $catPizza,
                'restaurant_id' => $resPizza,
                'name' => 'Pizza Bò BBQ',
                'description' => 'Thịt bò sốt BBQ thơm lừng.',
                'nutrition_info' => 'Năng lượng: 340 kcal/lát',
                'ingredients' => 'Bò Mỹ, sốt BBQ, hành tây, phô mai.',
                'base_price' => 135000,
                'image' => 'https://images.unsplash.com/photo-1594007654729-407eedc4be65?q=80&w=1200'
            ],
            [
                'category_id' => $catPizza,
                'restaurant_id' => $resPizza,
                'name' => 'Pizza Gà Nướng',
                'description' => 'Pizza gà nướng sốt mật ong.',
                'nutrition_info' => 'Năng lượng: 300 kcal/lát',
                'ingredients' => 'Thịt gà nướng, phô mai, bắp Mỹ.',
                'base_price' => 119000,
                'image' => 'https://images.unsplash.com/photo-1513104890138-7c749659a591?q=80&w=1200'
            ],

                // ================= BURGER =================
            [
                'category_id' => $catBurger,
                'restaurant_id' => $resBurger,
                'name' => 'Burger Gà Giòn',
                'description' => 'Phi lê gà chiên giòn cùng rau tươi.',
                'nutrition_info' => 'Năng lượng: 470 kcal',
                'ingredients' => 'Gà phi lê, bánh brioche, xà lách.',
                'base_price' => 75000,
                'image' => 'https://images.unsplash.com/photo-1606755962773-d324e0a13086?q=80&w=1200'
            ],
            [
                'category_id' => $catBurger,
                'restaurant_id' => $resBurger,
                'name' => 'Burger Double Beef',
                'description' => 'Hai lớp bò nướng đậm vị.',
                'nutrition_info' => 'Năng lượng: 700 kcal',
                'ingredients' => '2 miếng bò Úc, phô mai, sốt đặc biệt.',
                'base_price' => 119000,
                'image' => 'https://images.unsplash.com/photo-1550317138-10000687a72b?q=80&w=1200'
            ],
            [
                'category_id' => $catBurger,
                'restaurant_id' => $resBurger,
                'name' => 'Burger Bacon',
                'description' => 'Thịt xông khói giòn hấp dẫn.',
                'nutrition_info' => 'Năng lượng: 550 kcal',
                'ingredients' => 'Bò, bacon, cheddar, rau củ.',
                'base_price' => 99000,
                'image' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?q=80&w=1200'
            ],

                // ================= GÀ RÁN =================
            [
                'category_id' => $catGa,
                'restaurant_id' => $resChicken,
                'name' => 'Gà Rán Truyền Thống',
                'description' => 'Công thức giòn tan cổ điển.',
                'nutrition_info' => 'Năng lượng: 320 kcal',
                'ingredients' => 'Gà tươi, bột chiên giòn.',
                'base_price' => 89000,
                'image' => 'https://images.unsplash.com/photo-1513639776629-7b61b0ac49cb?q=80&w=1200'
            ],
            [
                'category_id' => $catGa,
                'restaurant_id' => $resChicken,
                'name' => 'Gà Sốt Phô Mai',
                'description' => 'Gà rán phủ sốt phô mai béo ngậy.',
                'nutrition_info' => 'Năng lượng: 410 kcal',
                'ingredients' => 'Gà rán, sốt cheddar.',
                'base_price' => 109000,
                'image' => 'https://images.unsplash.com/photo-1562967914-608f82629710?q=80&w=1200'
            ],
            [
                'category_id' => $catGa,
                'restaurant_id' => $resChicken,
                'name' => 'Gà Sốt Mật Ong',
                'description' => 'Vị ngọt dịu kết hợp lớp da giòn.',
                'nutrition_info' => 'Năng lượng: 380 kcal',
                'ingredients' => 'Gà rán, mật ong tự nhiên.',
                'base_price' => 99000,
                'image' => 'https://images.unsplash.com/photo-1626645738196-c2a7c87a8f58?q=80&w=1200'
            ],
            [
                'category_id' => $catGa,
                'restaurant_id' => $resChicken,
                'name' => 'Gà Sốt Tỏi Hàn Quốc',
                'description' => 'Sốt tỏi đậm vị phong cách Seoul.',
                'nutrition_info' => 'Năng lượng: 400 kcal',
                'ingredients' => 'Gà rán, tỏi, nước tương.',
                'base_price' => 105000,
                'image' => 'https://images.unsplash.com/photo-1608039755401-742074f0548d?q=80&w=1200'
            ],

                // ================= CƠM =================
            [
                'category_id' => $catCom,
                'restaurant_id' => $resComNgon,
                'name' => 'Cơm Gà Xối Mỡ',
                'description' => 'Gà chiên giòn ăn cùng cơm trắng.',
                'nutrition_info' => 'Năng lượng: 620 kcal',
                'ingredients' => 'Gà ta, cơm trắng, dưa leo.',
                'base_price' => 65000,
                'image' => 'https://images.unsplash.com/photo-1512058564366-18510be2db19?q=80&w=1200'
            ],
            [
                'category_id' => $catCom,
                'restaurant_id' => $resBeef,
                'name' => 'Cơm Bò Lúc Lắc',
                'description' => 'Bò mềm mọng cùng rau củ xào.',
                'nutrition_info' => 'Năng lượng: 700 kcal',
                'ingredients' => 'Bò Úc, cơm, hành tây, ớt chuông.',
                'base_price' => 89000,
                'image' => 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?q=80&w=1200'
            ],
            [
                'category_id' => $catCom,
                'restaurant_id' => $resComTam,
                'name' => 'Cơm Tấm Bì Chả',
                'description' => 'Đặc sản Sài Gòn truyền thống.',
                'nutrition_info' => 'Năng lượng: 680 kcal',
                'ingredients' => 'Cơm tấm, bì heo, chả trứng.',
                'base_price' => 75000,
                'image' => 'https://images.unsplash.com/photo-1604908176997-125f25cc6f3d?q=80&w=1200'
            ],
            [
                'category_id' => $catCom,
                'restaurant_id' => $resBeef,
                'name' => 'Cơm Bò Teriyaki',
                'description' => 'Phong cách Nhật Bản.',
                'nutrition_info' => 'Năng lượng: 650 kcal',
                'ingredients' => 'Thịt bò, sốt Teriyaki, mè rang.',
                'base_price' => 85000,
                'image' => 'https://images.unsplash.com/photo-1515003197210-e0cd71810b5f?q=80&w=1200'
            ],

                // ================= ĐỒ UỐNG =================
            [
                'category_id' => $catDoUong,
                'restaurant_id' => $resCoffee,
                'name' => 'Cà Phê Sữa Đá',
                'description' => 'Đậm đà chuẩn vị Việt Nam.',
                'nutrition_info' => 'Năng lượng: 180 kcal',
                'ingredients' => 'Cà phê Robusta, sữa đặc.',
                'base_price' => 35000,
                'image' => 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?q=80&w=1200'
            ],
            [
                'category_id' => $catDoUong,
                'restaurant_id' => $resCoffee,
                'name' => 'Trà Đào Cam Sả',
                'description' => 'Thức uống giải nhiệt được yêu thích.',
                'nutrition_info' => 'Năng lượng: 150 kcal',
                'ingredients' => 'Trà đen, đào ngâm, cam vàng, sả.',
                'base_price' => 45000,
                'image' => 'https://images.unsplash.com/photo-1499636136210-6f4ee915583e?q=80&w=1200'
            ],
            [
                'category_id' => $catDoUong,
                'restaurant_id' => $resCoffee,
                'name' => 'Matcha Latte',
                'description' => 'Matcha Nhật Bản kết hợp sữa tươi.',
                'nutrition_info' => 'Năng lượng: 220 kcal',
                'ingredients' => 'Matcha Uji, sữa tươi.',
                'base_price' => 55000,
                'image' => 'https://images.unsplash.com/photo-1515823064-d6e0c04616a7?q=80&w=1200'
            ]
        ];



        // Tự động sinh thêm 10 loại món khác để hệ thống có nhiều dữ liệu test
        for ($i = 1; $i <= 10; $i++) {
            $foods[] = [
                'category_id' => $catDoUong,
                'restaurant_id' => $resCoffee,
                'name' => 'Trà Trái Cây Nhiệt Đới Mix ' . $i,
                'description' => 'Giải khát tức thì với trà xanh thanh lọc và trái cây tươi cắt lát. Cung cấp nhiều Vitamin C, giúp tỉnh táo cả ngày dài.',
                'nutrition_info' => 'Năng lượng: 120 - 180 kcal | Giàu Vitamin C, chất chống oxy hóa, lượng đường tự nhiên từ trái cây.',
                'ingredients' => 'Trà xanh lài nguyên lá ủ lạnh, dâu tây Đà Lạt, kiwi, dưa hấu, cốt chanh dây, đường phèn tự nhiên, đá viên.',
                'base_price' => rand(25, 55) * 1000,
                'image' => 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?q=80&w=1200'
            ];
        }

        // INSERT TOÀN BỘ DATA
        foreach ($foods as $food) {
            $food['prep_time'] = rand(10, 35);      // Random thời gian chuẩn bị 10-35 phút
            $food['sold_count'] = rand(100, 5000);  // Random lượt bán 100-5000 lượt
            Food::create($food);
        }
    }
}