@extends('layouts.app')

@section('content')
    <section class="banner">
        <div>
            <h1>Đặt món ăn nhanh chóng</h1>
            <p>Giao diện hiện đại - thao tác đơn giản</p>
        </div>
    </section>

    <div class="container">
        <section class="categories" style="margin-bottom: 40px;">
            @foreach($categories as $category)
                <button onclick="window.location.href='{{ route('search', ['category_id' => $category->id]) }}'">
                    {{ $category->name }}
                </button>
            @endforeach
        </section>

        <div class="section-header">
            <div class="section-title" id="featured">🔥 Món ăn nổi bật</div>
            <a href="{{ route('search', ['sort' => 'rating']) }}" class="view-all">Xem tất cả &gt;</a>
        </div>
        <div class="slider-wrapper">
            <button class="slider-btn left" onclick="scrollSlider('featuredSlider',-300)">‹</button>
            <div class="slider" id="featuredSlider">
                @foreach($featuredFoods as $food)
                    <div class="food-card clickable-card" onclick="window.location.href='{{ route('food.detail', $food->id) }}'">
                        <img src="{{ $food->image ?? asset('images/default-food.jpg') }}" alt="{{ $food->name }}">
                        <div class="food-content">
                            <h3>{{ $food->name }}</h3>
                            <p class="food-desc">{{ $food->description }}</p>
                            
                            <div class="food-meta">
                                <span class="rating">
                                    <i class="fas fa-star" style="color: #ffc107;"></i> {{ '⭐ ' . ($food->restaurant->rating ?? '5.0') }}
                                </span>
                                <span class="time">
                                    <i class="far fa-clock"></i> {{ '🕒 ' . ($food->prep_time ?? 15) }} phút
                                </span>
                                <span class="distance">
                                    {{ $food->restaurant->distance ?? '2' }}km
                                </span>
                            </div>

                            <div class="food-price-row">
                                <div class="price">{{ number_format($food->base_price, 0, ',', '.') }}đ</div>
                                <div class="sold-count">
                                    {{ $food->sold_count >= 1000 ? round($food->sold_count / 1000, 1) . 'k+' : ($food->sold_count ?? 0) }} đã bán
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <button class="slider-btn right" onclick="scrollSlider('featuredSlider',300)">›</button>
        </div>

        <div class="section-header">
            <div class="section-title">📍 Món ăn gần bạn</div>
            <a href="{{ route('search', ['sort' => 'distance']) }}" class="view-all">Xem tất cả &gt;</a>
        </div>
        <div class="slider-wrapper">
            <button class="slider-btn left" onclick="scrollSlider('nearbySlider',-300)">‹</button>
            <div class="slider" id="nearbySlider">
                @foreach($nearbyFoods as $food)
                    <div class="food-card clickable-card" onclick="window.location.href='{{ route('food.detail', $food->id) }}'">
                        <img src="{{ $food->image ?? asset('images/default-food.jpg') }}" alt="{{ $food->name }}">
                        <div class="food-content">
                            <h3>{{ $food->name }}</h3>
                            <p class="food-desc">{{ $food->description }}</p>
                            
                            <div class="food-meta">
                                <span class="rating">
                                    <i class="fas fa-star" style="color: #ffc107;"></i> {{ '⭐ ' .  ($food->restaurant->rating ?? '5.0') }}
                                </span>
                                <span class="time">
                                    <i class="far fa-clock"></i> {{ '🕒 ' . ($food->prep_time ?? 15) }} phút
                                </span>
                                <span class="distance">
                                    {{ $food->restaurant->distance ?? '2' }}km
                                </span>
                            </div>

                            <div class="food-price-row">
                                <div class="price">{{ number_format($food->base_price, 0, ',', '.') }}đ</div>
                                <div class="sold-count">
                                    {{ $food->sold_count >= 1000 ? round($food->sold_count / 1000, 1) . 'k+' : ($food->sold_count ?? 0) }} đã bán
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <button class="slider-btn right" onclick="scrollSlider('nearbySlider',300)">›</button>
        </div>

        <div class="section-header">
            <div class="section-title">🏪 Quán ăn gần bạn</div>
            <a href="{{ route('restaurants.index') }}" class="view-all">Xem tất cả &gt;</a>
        </div>
        <div class="slider-wrapper">
            <button class="slider-btn left" onclick="scrollSlider('restaurantSlider',-300)">‹</button>
            <div class="slider" id="restaurantSlider">
                @foreach($restaurants as $restaurant)
                    <div class="food-card clickable-card" onclick="window.location.href='{{ route('search', ['restaurant_id' => $restaurant->id]) }}'">
                        <img src="{{ $restaurant->image ?? asset('images/default-restaurant.jpg') }}" alt="{{ $restaurant->name }}">
                        
                        <div class="food-content" style="justify-content: center; padding-bottom: 20px;">
                            <h3 style="margin-bottom: 10px;">{{ $restaurant->name }}</h3>
                            <p style="color: #666; font-size: 14px;">
                                <i class="fas fa-star" style="color: #ffc107;"></i> {{'⭐ ' . ($restaurant->rating ?? '4.5') }} 
                                &nbsp;•&nbsp; Cách bạn {{ $restaurant->distance ?? '1.5' }}km
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
            <button class="slider-btn right" onclick="scrollSlider('restaurantSlider',300)">›</button>
        </div>
    </div>
@endsection