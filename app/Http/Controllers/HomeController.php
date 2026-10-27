<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Food;
use App\Models\Restaurant;

class HomeController extends Controller
{
    public function index()
    {
        // Lấy dữ liệu hiển thị giao diện trang chủ
        $categories = Category::all();
        $featuredFoods = Food::with('restaurant')->take(6)->get(); 
        $nearbyFoods = Food::with('restaurant')->skip(4)->take(6)->get(); // Lấy các món khác làm món gần bạn
        $restaurants = Restaurant::take(6)->get();

        return view('home', compact('categories', 'featuredFoods', 'nearbyFoods', 'restaurants'));
    }
}