@extends('layouts.app')

@section('content')
    <style>
        /* Hiệu ứng hover cho các nút trong Modal */
        .btn-modal-cancel:hover {
            background-color: #e2e2e2 !important;
        }

        .btn-modal-delete:hover {
            background-color: #c82333 !important;
        }
    </style>

    <div class="cart-page" style="max-width: 1000px; margin: 40px auto; padding: 20px;">
        <div class="cart-header"
            style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 30px;">
            <div class="cart-title"
                style="display: inline-flex; align-items: center; gap: 12px; font-size: 28px; font-weight: bold; color: #000;">
                <i class="fas fa-shopping-cart" style="font-size: 32px; color: #ff5722;"></i>
                <span>Giỏ hàng</span>
            </div>
            <div class="cart-count"
                style="background: #ffceb3; color: #ff5722; padding: 5px 15px; border-radius: 20px; font-weight: bold;">
                {{ count($cart ?? []) }} món
            </div>
        </div>

        @if(empty($cart))
            <p class="empty-cart"
                style="text-align: center; padding: 50px; background: #fff; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                Giỏ hàng trống. <a href="{{ route('home') }}"
                    style="color: #ff5722; font-weight: bold; text-decoration: none;">Tiếp tục chọn món</a>
            </p>
        @else
            <div class="cart-items">
                @php $total = 0; @endphp
                @foreach($cart as $cartId => $item)
                    @php $total += $item['price'] * $item['quantity']; @endphp

                    <div class="cart-item"
                        style="display: flex; align-items: center; background: #fafafa; border-radius: 12px; padding: 20px; margin-bottom: 20px; border: 1px solid #eee;">
                        <div style="margin-right: 20px;">
                            <input type="checkbox" class="item-checkbox" checked
                                data-price="{{ $item['price'] * $item['quantity'] }}"
                                style="transform: scale(1.5); accent-color: #ff5722; cursor: pointer;" onchange="updateCartTotal()">
                        </div>

                        <img src="{{ $item['image'] ?? asset('images/default-food.jpg') }}" alt="Food Image"
                            style="width: 100px; height: 100px; border-radius: 10px; object-fit: cover; margin-right: 20px;">

                        <div class="item-info" style="flex: 1;">
                            <div class="item-name" style="font-size: 22px; font-weight: bold; color: #000; margin-bottom: 5px;">
                                {{ $item['base_name'] ?? $item['name'] }}
                            </div>

                            <div class="item-restaurant" style="font-size: 14px; color: #555; margin-bottom: 5px;">
                                🏪 {{ $item['restaurant'] ?? 'Quán ăn đối tác' }}
                            </div>

                            <div class="item-variant" style="font-size: 14px; color: #333; margin-bottom: 10px;">
                                {{ $item['size'] ?? '' }}
                                @if(!empty($item['toppings']))
                                    &nbsp;•&nbsp; {{ $item['toppings'] }}
                                @endif

                                @if(!empty($item['note']))
                                    <br><i style="color: #888;">Ghi chú: {{ $item['note'] }}</i>
                                @endif
                            </div>

                            <div class="item-unit-price" style="font-size: 22px; font-weight: bold; color: #ff5722;">
                                {{ number_format($item['price'], 0, ',', '.') }}đ
                            </div>
                        </div>

                        <div class="item-actions" style="display: flex; flex-direction: column; align-items: flex-end; gap: 15px;">
                            <form action="{{ route('cart.update') }}" method="POST"
                                style="display: flex; align-items: center; background: #ff5722; border-radius: 6px; overflow: hidden; height: 35px;">
                                @csrf
                                <input type="hidden" name="cart_id" value="{{ $cartId }}">
                                <button type="submit" name="quantity" value="{{ $item['quantity'] - 1 }}"
                                    style="width: 35px; background: none; border: none; color: white; font-size: 18px; cursor: pointer; font-weight: bold;"
                                    {{ $item['quantity'] <= 1 ? 'disabled' : '' }}>-</button>
                                <span
                                    style="width: 40px; text-align: center; color: #000; background: #fff; font-weight: bold; line-height: 35px;">{{ $item['quantity'] }}</span>
                                <button type="submit" name="quantity" value="{{ $item['quantity'] + 1 }}"
                                    style="width: 35px; background: none; border: none; color: white; font-size: 18px; cursor: pointer; font-weight: bold;">+</button>
                            </form>

                            <div class="item-total-price" style="font-size: 22px; font-weight: bold; color: #000;">
                                {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}đ
                            </div>

                            <button type="button"
                                onclick="confirmDelete('{{ $cartId }}', '{{ $item['base_name'] ?? $item['name'] }}')"
                                style="background: transparent; color: #ff5722; border: none; font-size: 16px; cursor: pointer; font-weight: bold;">
                                Xóa
                            </button>

                            <form id="delete-form-{{ $cartId }}" action="{{ route('cart.remove') }}" method="POST"
                                style="display: none;">
                                @csrf
                                <input type="hidden" name="cart_id" value="{{ $cartId }}">
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="cart-summary"
                style="margin-top: 30px; padding: 25px; border-radius: 12px; background: #fff; border: 1px solid #eee; box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
                <div class="summary-row"
                    style="display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 16px; color: #555;">
                    <span>Tạm tính:</span>
                    <span id="subtotal">0đ</span>
                </div>
                <div class="summary-row"
                    style="display: flex; justify-content: space-between; margin-bottom: 20px; font-size: 16px; color: #555;">
                    <span>Phí giao hàng:</span>
                    <span id="deliveryFee">20.000đ</span>
                </div>

                <hr style="border: none; border-top: 1px dashed #ddd; margin-bottom: 20px;">

                <div class="summary-row total" style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 20px; font-weight: bold; color: #333;">Tổng cộng:</span>
                    <div style="display: flex; align-items: center; gap: 30px;">
                        <span id="total" style="font-size: 22px; font-weight: bold; color: #ff5722;">0đ</span>
                        <button onclick="window.location.href='{{ route('checkout.index') }}'"
                            style="background: #ff5722; color: white; padding: 14px 28px; border: none; border-radius: 8px; font-size: 16px; font-weight: bold; cursor: pointer; transition: 0.3s;">
                            Tiến hành đặt hàng
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div id="customConfirmModal"
        style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:10000; align-items:center; justify-content:center;">
        <div
            style="background:#fff; padding:30px; border-radius:12px; width:400px; text-align:center; box-shadow: 0 5px 20px rgba(0,0,0,0.15);">
            <div style="margin-bottom: 15px;">
                <i class="fas fa-trash-alt" style="color: #dc3545; font-size: 50px;"></i>
            </div>
            <h3 style="margin-bottom:10px; font-size:20px; font-weight:bold; color:#333;">Xác nhận xóa</h3>
            <p style="font-size: 15px; color: #555; margin-bottom: 25px; line-height: 1.5;" id="confirmMessage"></p>

            <div style="display: flex; gap: 15px; justify-content: center;">
                <button type="button" onclick="closeConfirmModal()" class="btn-modal-cancel"
                    style="background:#f1f1f1; color:#333; border:none; padding:12px 25px; border-radius:8px; font-weight:bold; cursor:pointer; font-size: 15px; flex: 1; transition: 0.3s;">Hủy</button>
                <button type="button" id="btnConfirmDelete" class="btn-modal-delete"
                    style="background:#dc3545; color:white; border:none; padding:12px 25px; border-radius:8px; font-weight:bold; cursor:pointer; font-size: 15px; flex: 1; transition: 0.3s;">Xóa
                    món</button>
            </div>
        </div>
    </div>

    <script>
        // ----- LOGIC CUSTOM CONFIRM MODAL -----
        let currentDeleteCartId = null;

        // Thay thế hàm confirm() mặc định bằng Custom Modal
        function confirmDelete(cartId, foodName) {
            currentDeleteCartId = cartId; // Lưu ID món ăn cần xóa
            document.getElementById('confirmMessage').innerHTML = "Bạn có chắc chắn muốn xóa món <strong>" + foodName + "</strong> khỏi giỏ hàng?";
            document.getElementById('customConfirmModal').style.display = 'flex';
        }

        // Hàm đóng Modal Hủy
        function closeConfirmModal() {
            document.getElementById('customConfirmModal').style.display = 'none';
            currentDeleteCartId = null; // Reset lại ID
        }

        // Sự kiện khi bấm nút Xóa trong Modal
        document.getElementById('btnConfirmDelete').addEventListener('click', function () {
            if (currentDeleteCartId) {
                document.getElementById('delete-form-' + currentDeleteCartId).submit();
            }
        });

        // ----- LOGIC TÍNH TOÁN GIỎ HÀNG -----
        function updateCartTotal() {
            let subtotal = 0;
            let deliveryFee = 20000; // Mặc định phí ship 20k

            let checkboxes = document.querySelectorAll('.item-checkbox');
            let hasChecked = false;

            checkboxes.forEach(function (checkbox) {
                if (checkbox.checked) {
                    subtotal += parseInt(checkbox.getAttribute('data-price'));
                    hasChecked = true;
                }
            });

            // Nếu không có món nào được chọn, phí ship bằng 0
            if (!hasChecked) {
                deliveryFee = 0;
            }

            let total = subtotal + deliveryFee;

            document.getElementById('subtotal').innerText = new Intl.NumberFormat('vi-VN').format(subtotal) + 'đ';
            document.getElementById('deliveryFee').innerText = new Intl.NumberFormat('vi-VN').format(deliveryFee) + 'đ';
            document.getElementById('total').innerText = new Intl.NumberFormat('vi-VN').format(total) + 'đ';
        }

        // Tự động tính tiền khi load trang xong
        window.onload = function () {
            if (document.querySelectorAll('.item-checkbox').length > 0) {
                updateCartTotal();
            }
        };
    </script>
@endsection