@extends('layouts.app')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

@section('content')
<style>
    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    input[type=number] {
        -moz-appearance: textfield;
    }
</style>
<div class="container detail-page-wrapper">
    <div class="back-btn-wrapper">
        <a href="{{ route('search', ['restaurant_id' => $food->restaurant_id]) }}" class="back-btn">
            <i class="fas fa-arrow-left"></i> Quay lại thực đơn quán
        </a>
    </div>

    <div class="detail-layout">
        <div class="detail-left">
            <div class="image-gallery">
                <img src="{{ $food->image ?? asset('images/default-food.jpg') }}" alt="{{ $food->name }}" class="main-img">
                <div class="thumbnail-list">
                    <img src="{{ $food->image ?? asset('images/default-food.jpg') }}" class="active">
                    <img src="{{ $food->image ?? asset('images/default-food.jpg') }}">
                    <img src="{{ $food->image ?? asset('images/default-food.jpg') }}">
                </div>
            </div>

            <div class="food-accordions">
                <div class="accordion-item">
                    <button class="accordion-header active" onclick="toggleAccordion(this)" style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                        <span>📝 Mô tả món ăn</span>
                        <i class="fas fa-chevron-up"></i>
                    </button>
                    <div class="accordion-body" style="display: block;">
                        <p>{{ $food->description ?? 'Đang cập nhật mô tả...' }}</p>
                    </div>
                </div>

                <div class="accordion-item">
                    <button class="accordion-header" onclick="toggleAccordion(this)" style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                        <span>🥗 Thông tin dinh dưỡng</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="accordion-body" style="display: none;">
                        <p>{{ $food->nutrition ?? 'Hàm lượng calo thấp, giàu protein và vitamin nhóm B.' }}</p>
                    </div>
                </div>

                <div class="accordion-item">
                    <button class="accordion-header" onclick="toggleAccordion(this)" style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                        <span>🥕 Nguyên liệu chế biến</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="accordion-body" style="display: none;">
                        <p>{{ $food->ingredients ?? 'Nguyên liệu tươi sạch được tuyển chọn nghiêm ngặt từ các trang trại đối tác.' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="detail-right">
            <h1 class="food-title">{{ $food->name }}</h1>
            <div class="restaurant-name">🍴 {{ $food->restaurant->name ?? 'Quán ăn đối tác' }}</div>
            <div class="food-meta-info">
                ⭐ {{ $food->reviews->isNotEmpty() ? number_format($food->reviews->avg('rating'), 1) : ($food->restaurant->rating ?? 'Chưa có đánh giá') }} • {{ $food->restaurant->distance ?? '2.1' }}km • {{ $food->prep_time ?? '25' }} phút
            </div>

            <div class="price-display">
                @if($food->sale_price)
                    <span class="old-price">{{ number_format($food->base_price, 0, ',', '.') }}đ</span>
                    <span class="new-price" id="foodPrice" data-base="{{ $food->sale_price }}">{{ number_format($food->sale_price, 0, ',', '.') }}đ</span>
                @else
                    <span class="new-price" id="foodPrice" data-base="{{ $food->base_price }}">{{ number_format($food->base_price, 0, ',', '.') }}đ</span>
                @endif
            </div>

            <form action="{{ route('cart.add') }}" method="POST" id="addToCartForm">
                @csrf
                <input type="hidden" name="food_id" value="{{ $food->id }}">

                @if($food->sizes && $food->sizes->count() > 0)
                <div class="form-section">
                    <h3>1. Chọn size / Phân loại</h3>
                    <div class="option-grid">
                        @foreach($food->sizes as $index => $size)
                        <label class="custom-radio-box">
                            <input type="radio" name="size" value="{{ $size->name }}|{{ $size->extra_price }}" data-price="{{ $size->extra_price }}" onchange="calculateTotal()" {{ $index == 0 ? 'checked' : '' }}>
                            <span class="box-content">
                                <span class="opt-name">{{ $size->name }}</span>
                                <span class="opt-price">+{{ number_format($size->extra_price, 0, ',', '.') }}đ</span>
                            </span>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endif

                @if($food->toppings && $food->toppings->count() > 0)
                <div class="form-section">
                    <h3>2. Chọn Món thêm / Topping</h3>
                    <div class="option-list">
                        @foreach($food->toppings as $topping)
                        <label class="custom-checkbox-row">
                            <input type="checkbox" name="toppings[]" value="{{ $topping->name }}|{{ $topping->extra_price }}" data-price="{{ $topping->extra_price }}" onchange="calculateTotal()">
                            <span class="row-content">
                                <span class="opt-name">{{ $topping->name }}</span>
                                <span class="opt-price">+{{ number_format($topping->extra_price, 0, ',', '.') }}đ</span>
                            </span>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="quantity-section" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 25px; background: #fff; padding: 10px 0;">
                    <h3 style="margin: 0; font-size: 16px; font-weight: bold; color: #333;">3. Số lượng</h3>
                    
                    <div class="qty-control" style="display: inline-flex; align-items: center; background: #fff; border-radius: 6px; overflow: hidden; border: 1px solid #ddd; height: 38px; box-sizing: border-box; padding: 0; margin: 0;">
                        <button type="button" onclick="updateQty(-1)" style="width: 38px; height: 100%; background: #FD5532; border: none; font-size: 18px; cursor: pointer; font-weight: bold; color: #ffffff; outline: none; margin: 0; padding: 0; display: flex; align-items: center; justify-content: center; border-right: 1px solid #ddd; box-sizing: border-box;">-</button>
                        
                        <input type="number" name="quantity" id="qtyInput" value="1" min="1" oninput="handleQtyInput(this)" 
                        style="width: 60px; height: 100%; text-align: center; border: none; background: #fff; font-weight: bold; font-size: 15px; outline: none; margin: 0; padding: 0; box-sizing: border-box; 
                                pointer-events: auto !important; -webkit-user-select: text !important; -moz-user-select: text !important; user-select: text !important; cursor: text !important;">
                        
                        <button type="button" onclick="updateQty(1)" style="width: 38px; height: 100%; background: #FD5532; border: none; font-size: 18px; cursor: pointer; font-weight: bold; color: #ffffff; outline: none; margin: 0; padding: 0; display: flex; align-items: center; justify-content: center; border-left: 1px solid #ddd; box-sizing: border-box;">+</button>
                    </div>
                </div>

                <div class="note-section" style="margin-bottom: 25px;">
                    <h3 style="font-size: 16px; font-weight: bold; color: #333; margin-bottom: 10px;">4. Ghi chú món ăn</h3>
                    <textarea name="note" id="foodNote" rows="4" placeholder="Ghi chú thêm cho nhà hàng (VD: Không hành, cay vừa...)" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; resize: none; box-sizing: border-box; outline: none; transition: 0.3s;"></textarea>
                </div>

                <div class="summary-box">
                    <div class="summary-text">
                        <strong>Tạm tính</strong><br>
                        <!-- <small>(Đã bao gồm VAT nếu có)</small> -->
                    </div>
                    <div class="summary-total" id="totalPriceDisplay">
                        {{ number_format($food->sale_price ?? $food->base_price, 0, ',', '.') }}đ
                    </div>
                </div>

                <div class="action-buttons-row">
                    <button type="submit" name="action" value="add_cart" class="btn-add-cart">🛒 Thêm vào giỏ hàng</button>
                    <button type="submit" name="action" value="buy_now" class="btn-buy-now">Mua ngay</button>
                </div>
            </form>
        </div>
    </div>

    @if($food->reviews->isNotEmpty())
        <section style="max-width:1100px;margin:32px auto">
            <h2>Đánh giá món ăn</h2>
            @foreach($food->reviews->sortByDesc('created_at') as $review)
                <article style="border-top:1px solid #ddd;padding:16px 0">
                    <strong>{{ $review->user->name ?? 'Khách hàng' }}</strong>
                    <span> · {{ $review->rating }} / 5 sao</span>
                    @if($review->comment)<p>{{ $review->comment }}</p>@endif
                    @foreach($review->images ?? [] as $image)
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($image) }}" alt="Ảnh đánh giá" width="100" height="100" style="object-fit:cover">
                    @endforeach
                </article>
            @endforeach
        </section>
    @endif
</div>
@endsection

@push('scripts')
<script>
    // Xử lý đóng/mở Accordion (Tự động đóng tab cũ khi mở tab mới)
    // Xử lý khi người dùng nhấn nút + hoặc -
    function updateQty(change) {
        let input = document.getElementById('qtyInput');
        let newValue = (parseInt(input.value) || 1) + change;
        if(newValue >= 1) {
            input.value = newValue;
            calculateTotal();
        }
    }

    // Xử lý kiểm soát dữ liệu khi người dùng tự nhập tay từ bàn phím
    function handleQtyInput(input) {
        // Chỉ cho phép nhập các ký tự số tự nhiên
        input.value = input.value.replace(/[^0-9]/g, '');
        
        let val = parseInt(input.value);
        if (!isNaN(val) && val >= 1) {
            calculateTotal();
        }
    }

    // Khi người dùng click ra ngoài ô nhập liệu (blur), nếu để trống hoặc số < 1 thì tự reset về 1
    document.getElementById('qtyInput').addEventListener('blur', function() {
        let val = parseInt(this.value);
        if (isNaN(val) || val < 1) {
            this.value = 1;
            calculateTotal();
        }
    });

    // Hàm xử lý đóng mở Accordion và tự động đổi chiều icon mũi tên
    function toggleAccordion(btn) {
        let item = btn.closest('.accordion-item');
        let body = item.querySelector('.accordion-body');
        let icon = btn.querySelector('i');
        
        if (body.style.display === "block") {
            body.style.display = "none";
            icon.className = "fas fa-chevron-down";
            btn.classList.remove("active");
        } else {
            body.style.display = "block";
            icon.className = "fas fa-chevron-up";
            btn.classList.add("active");
        }
    }

    function calculateTotal() {
        let basePrice = parseInt(document.getElementById('foodPrice').getAttribute('data-base'));
        let sizeInput = document.querySelector('input[name="size"]:checked');
        let sizePrice = sizeInput ? parseInt(sizeInput.getAttribute('data-price')) : 0;
        
        let toppingPrice = 0;
        document.querySelectorAll('input[name="toppings[]"]:checked').forEach(function(cb) {
            toppingPrice += parseInt(cb.getAttribute('data-price'));
        });
        
        let qty = parseInt(document.getElementById('qtyInput').value);
        let total = (basePrice + sizePrice + toppingPrice) * qty;
        
        document.getElementById('totalPriceDisplay').innerText = new Intl.NumberFormat('vi-VN').format(total) + 'đ';
    }

    window.onload = function() { calculateTotal(); };
</script>
@endpush