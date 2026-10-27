<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Food;
use App\Models\Category;
use App\Models\Restaurant;
use App\Strategies\SortByPrice;
use App\Strategies\SortByRating;
use App\Strategies\SortByDistance;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $keyword = $request->input('keyword');
        $categoryId = $request->input('category_id');
        $restaurantId = $request->input('restaurant_id');
        $sortType = $request->input('sort', 'all'); // Mặc định là 'all'

        $query = Food::with('restaurant');
        $title = 'Kết quả tìm kiếm';

        // 1. Lọc MÓN ĂN theo từ khóa (Dùng Closure nhóm để bảo toàn logic với bộ lọc sau)
        if ($keyword) {
            $query->where(function($q) use ($keyword) {
                // SỬA Ở ĐÂY: Thêm 'food.' vào trước 'name' và 'description'
                $q->where('food.name', 'like', "%{$keyword}%")
                ->orWhere('food.description', 'like', "%{$keyword}%");
            });
        }

        // 2. Lọc theo danh mục
        if ($categoryId) {
            $query->where('food.category_id', $categoryId); 
            $category = Category::find($categoryId);
            if ($category) $title = '🍜 Danh mục: ' . $category->name;
        }

        // 3. Lọc theo quán ăn
        if ($restaurantId) {
            $query->where('food.restaurant_id', $restaurantId);
            $restaurant = Restaurant::find($restaurantId);
            if ($restaurant) $title = '🏪 Thực đơn quán: ' . $restaurant->name;
        }

        // 4. ÁP DỤNG STRATEGY PATTERN ĐỂ SẮP XẾP MÓN ĂN
        $strategy = match($sortType) {
            'price' => new SortByPrice(),
            'rating' => new SortByRating(),
            'distance' => new SortByDistance(),
            default => null,
        };

        if ($strategy) {
            $query = $strategy->sort($query);
        }

        $foods = $query->paginate(8)->withQueryString();

        // 5. TÌM KIẾM QUÁN ĂN RIÊNG BIỆT (Danh mục thứ 2)
        $restaurants = collect();
        if ($keyword) {
            $restaurants = Restaurant::where('name', 'like', "%{$keyword}%")->get();
        }

        return view('food.search', compact('foods', 'restaurants', 'keyword', 'categoryId', 'restaurantId', 'sortType', 'title'));
    }
}