@extends('layouts.app')

@section('content')
<div class="container">
    <section class="search-result-section">
        <div class="search-result-header">
            <h2>🏪 Tất cả quán ăn đối tác</h2>
            <div class="result-count">
                {{ $restaurants->count() }} quán
            </div>
        </div>

        <form action="{{ route('restaurants.index') }}" method="GET" class="search-filter">
            <button type="submit" name="sort" value="all" class="{{ $sortType == 'all' ? 'active' : '' }}">
                Tất cả quán
            </button>
            <button type="submit" name="sort" value="distance" class="{{ $sortType == 'distance' ? 'active' : '' }}">
                Gần bạn nhất
            </button>
            <button type="submit" name="sort" value="rating" class="{{ $sortType == 'rating' ? 'active' : '' }}">
                Đánh giá cao
            </button>
        </form>

        <div class="search-grid" style="margin-top: 25px;">
            @foreach($restaurants as $restaurant)
                <div class="food-card">
                    <img src="{{ $restaurant->image ?? asset('images/default-restaurant.jpg') }}" alt="{{ $restaurant->name }}">
                    <div class="food-content">
                        <h3>{{ $restaurant->name }}</h3>
                        
                        <div class="food-meta" style="margin-bottom: 5px;">
                            <span>⭐ {{ $restaurant->rating }}</span>
                            <span>📍 {{ $restaurant->distance }}km</span>
                        </div>
                        
                        <button onclick="window.location.href='{{ route('search', ['restaurant_id' => $restaurant->id]) }}'" style="margin-top: 15px;">
                            Xem thực đơn
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
</div>
@endsection