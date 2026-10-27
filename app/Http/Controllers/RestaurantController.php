<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Restaurant;
use App\Strategies\SortRestaurantByDistance;
use App\Strategies\SortRestaurantByRating;

class RestaurantController extends Controller
{
    public function index(Request $request)
    {
        $sortType = $request->input('sort', 'all'); // Mặc định hiển thị tất cả
        $query = Restaurant::query();

        // Áp dụng Strategy Pattern dựa trên lựa chọn của người dùng
        $strategy = match($sortType) {
            'distance' => new SortRestaurantByDistance(),
            'rating'   => new SortRestaurantByRating(),
            default    => null,
        };

        if ($strategy) {
            $query = $strategy->sort($query);
        }

        $restaurants = $query->get();

        return view('restaurant.index', compact('restaurants', 'sortType'));
    }
}