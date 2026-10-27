@extends('layouts.app')

@section('content')
<style>
    .restaurant-search-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease !important;
    }
    .restaurant-search-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08) !important;
        border-color: #ff5722 !important;
    }
    .restaurant-search-card img {
        transition: transform 0.3s ease !important;
    }
    .restaurant-search-card:hover img {
        transform: scale(1.05);
    }
</style>
<div class="container">
    <section class="search-result-section" id="searchResultSection">
        <div class="search-result-header">
            <h2>
                {{ $title }} </h2>
            <div class="result-count">
                {{ $foods->count() }} kết quả
            </div>
        </div>

        <form action="{{ route('search') }}" method="GET" class="search-filter">
    
            @if($keyword) <input type="hidden" name="keyword" value="{{ $keyword }}"> @endif
            @if($categoryId) <input type="hidden" name="category_id" value="{{ $categoryId }}"> @endif
            @if($restaurantId) <input type="hidden" name="restaurant_id" value="{{ $restaurantId }}"> @endif

            <button type="submit" name="sort" value="all" class="{{ $sortType == 'all' ? 'active' : '' }}">
                Tất cả
            </button>
            
            <button type="submit" name="sort" value="distance" class="{{ $sortType == 'distance' ? 'active' : '' }}">
                Gần bạn
            </button>
            
            <button type="submit" name="sort" value="price" class="{{ $sortType == 'price' ? 'active' : '' }}">
                Giá thấp
            </button>
            
            <button type="submit" name="sort" value="rating" class="{{ $sortType == 'rating' ? 'active' : '' }}">
                Đánh giá cao
            </button>
        </form>

        @if($keyword && !$restaurants->isEmpty())
            <div class="restaurant-results-category" style="margin: 30px 0 40px 0;">
                <h3 style="font-size: 18px; font-weight: bold; color: #222; margin-bottom: 15px; padding-bottom: 8px; border-bottom: 2px solid #ff5722; display: inline-block;">
                    🏪 Quán ăn kết quả ({{ $restaurants->count() }})
                </h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 20px;">
                    @foreach($restaurants as $res)
                        <div class="restaurant-search-card" style="background: white; border-radius: 12px; padding: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.04); display: flex; align-items: center; gap: 15px; border: 1px solid #eee;">
                            <img src="{{ $res->image ?? 'https://ui-avatars.com/api/?name='.urlencode($res->name).'&background=ffceb3&color=ff5722&size=100' }}" alt="{{ $res->name }}" style="width: 65px; height: 65px; border-radius: 8px; object-fit: cover;">
                            <div style="flex: 1;">
                                <h4 style="margin: 0 0 6px 0; font-size: 15px; font-weight: bold; color: #111;">{{ $res->name }}</h4>
                                <a href="{{ route('search', ['restaurant_id' => $res->id]) }}" style="color: #ff5722; font-size: 13px; font-weight: bold; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                                    Xem thực đơn <i class="fas fa-arrow-right" style="font-size: 11px;"></i>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="search-grid" style="display: grid !important; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)) !important; gap: 20px !important;">
            @forelse($foods as $food)
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
            @empty
                <div class="empty-search-state" style="grid-column: 1 / -1; text-align: center; padding: 60px 20px;">
                    <i class="fas fa-search" style="font-size: 60px; color: #ccc; margin-bottom: 20px;"></i>
                    <h2 style="color: #333; margin-bottom: 10px;">Không tìm thấy kết quả phù hợp</h2>
                    <p style="color: #777; margin-bottom: 30px;">Hãy thử tìm kiếm bằng một từ khóa khác nhé!</p>
                    
                    <div class="suggested-keywords" style="margin-bottom: 30px;">
                        <span style="color: #555; margin-right: 10px;">Từ khóa phổ biến:</span>
                        <a href="{{ route('search', ['keyword' => 'Pizza']) }}" class="keyword-tag">Pizza</a>
                        <a href="{{ route('search', ['keyword' => 'Burger']) }}" class="keyword-tag">Burger</a>
                        <a href="{{ route('search', ['keyword' => 'Cơm']) }}" class="keyword-tag">Cơm</a>
                        <a href="{{ route('search', ['keyword' => 'Gà rán']) }}" class="keyword-tag">Gà rán</a>
                    </div>

                    <a href="{{ route('home') }}" class="btn-primary" style="padding: 12px 25px; border-radius: 8px; text-decoration: none;">
                        Quay về trang chủ
                    </a>
                </div>
            @endforelse
        </div>

        @if($foods->hasMorePages())
        <div style="text-align: center; margin-top: 40px;" id="loadMoreWrapper">
            <button onclick="loadMoreFoods('{{ $foods->nextPageUrl() }}')" class="btn-outline" style="padding: 12px 40px; border-radius: 30px; font-size: 16px; font-weight: bold; cursor: pointer;">
                Xem thêm ⏷
            </button>
        </div>
        @endif
    </section>
</div>
<script>
    function loadMoreFoods(url) {
        // Đổi chữ trên nút để báo hiệu đang tải
        const btn = document.querySelector('#loadMoreWrapper button');
        btn.innerText = "Đang tải...";
        btn.disabled = true;

        fetch(url)
            .then(response => response.text())
            .then(html => {
                // Tạo một DOM ảo để đọc HTML trả về
                let parser = new DOMParser();
                let doc = parser.parseFromString(html, 'text/html');
                
                // Lấy các thẻ card món ăn ở trang tiếp theo
                let newItems = doc.querySelectorAll('.search-grid .food-card');
                let grid = document.querySelector('.search-grid');
                
                // Trổ các món mới xuống dưới lưới hiện tại
                newItems.forEach(item => grid.appendChild(item));

                // Cập nhật lại nút Xem thêm (cho trang kế tiếp)
                let newLoadMore = doc.querySelector('#loadMoreWrapper');
                let currentLoadMore = document.querySelector('#loadMoreWrapper');
                
                if (newLoadMore) {
                    currentLoadMore.innerHTML = newLoadMore.innerHTML;
                } else {
                    currentLoadMore.remove(); // Xóa nút nếu đã hết trang
                }
            })
            .catch(error => {
                console.error("Lỗi khi tải thêm món ăn: ", error);
                btn.innerText = "Xem thêm ⏷";
                btn.disabled = false;
            });
    }
</script>
@endsection