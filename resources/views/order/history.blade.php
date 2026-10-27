@extends('layouts.app')

@section('content')
<div class="history-container">
    <div class="history-header">
        <h2>📦 Lịch sử đơn hàng</h2>
        @if($errors->any())
            <div role="alert" style="color:#b42318">{{ $errors->first() }}</div>
        @endif
    </div>

    <div class="history-filters">
        <a href="{{ route('orders.history', ['status' => 'all']) }}" class="filter-btn {{ $statusFilter == 'all' ? 'active' : '' }}">Tất cả</a>
        <a href="{{ route('orders.history', ['status' => 'pending']) }}" class="filter-btn {{ $statusFilter == 'pending' ? 'active' : '' }}">Chờ xác nhận</a>
        <a href="{{ route('orders.history', ['status' => 'delivering']) }}" class="filter-btn {{ $statusFilter == 'delivering' ? 'active' : '' }}">Đang giao</a>
        <a href="{{ route('orders.history', ['status' => 'completed']) }}" class="filter-btn {{ $statusFilter == 'completed' ? 'active' : '' }}">Đã giao</a>
        <a href="{{ route('orders.history', ['status' => 'cancelled']) }}" class="filter-btn {{ $statusFilter == 'cancelled' ? 'active' : '' }}">Đã hủy</a>
    </div>

    @if($orders->isEmpty())
        <div class="empty-history">
            <p>Bạn chưa có đơn hàng nào.</p>
            <a href="{{ route('home') }}" class="btn-primary">Đặt món ngay</a>
        </div>
    @else
        <div class="order-list">
            @foreach($orders as $order)
                @php
                    // Tạo chuỗi tóm tắt món ăn
                    $summaryItems = [];
                    foreach($order->items as $item) {
                        $summaryItems[] = $item->quantity . 'x ' . $item->food_name;
                    }
                    $summaryText = implode(', ', $summaryItems);

                    // TRUY XUẤT THÔNG TIN QUÁN ĂN THẬT TỪ DATABASE
                    $restaurantName = 'MlemNow - Quán ăn';
                    $restaurantImage = 'https://ui-avatars.com/api/?name=Mlem+Now&background=ffceb3&color=ff5722&size=120'; // Ảnh mặc định an toàn ko bị lỗi

                    $firstItem = $order->items->first();
                    if ($firstItem && $firstItem->food && $firstItem->food->restaurant) {
                        $restaurantName = $firstItem->food->restaurant->name;
                        $restaurantImage = $firstItem->food->restaurant->image ?? $restaurantImage;
                    }
                @endphp

                <div class="order-card">
                    <img src="{{ $restaurantImage }}" alt="{{ $restaurantName }}" class="order-img" onerror="this.src='https://ui-avatars.com/api/?name=Error&background=ddd&color=333&size=120'">
                    
                    <div class="order-info">
                        <h3>{{ $restaurantName }}</h3>
                        <p class="order-meta">
                            Mã đơn: #{{ $order->order_code }} | {{ $order->created_at->format('d/m/Y, H:i A') }}
                        </p>
                        
                        <div class="order-summary-box">
                            {{ $summaryText }}
                        </div>

                        <div class="order-status">
                            @if($order->status == 'delivering')
                                <span class="badge warning">Đang giao</span>
                            @elseif($order->status == 'completed')
                                <span class="badge success">Đã giao</span>
                            @elseif($order->status == 'pending')
                                <span class="badge default">Chờ xác nhận</span>
                            @else
                                <span class="badge default">{{ $order->getStateLabel() ?? 'Chờ xác nhận' }}</span>
                            @endif
                            
                            @if($order->status == 'delivering')
                                    <form action="{{ route('order.confirm', $order->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn-primary" style="background-color: #ff5722; border-color: #ff5722; color: white;">Đã nhận được hàng</button>
                                    </form>
                            @endif
                        </div>
                        @if($order->status === 'pending')
                            <button type="button" class="btn-outline" onclick="document.getElementById('cancel-order-{{ $order->id }}').showModal()">Hủy đơn hàng</button>
                            <dialog id="cancel-order-{{ $order->id }}" style="width:min(460px,calc(100% - 32px));border:0;border-radius:10px;padding:24px">
                                <form action="{{ route('orders.cancel', $order->id) }}" method="POST" class="cancel-order-form" novalidate>
                                    @csrf
                                    <input type="hidden" name="cancel_order_id" value="{{ $order->id }}">
                                    <h3>Hủy đơn hàng #{{ $order->order_code }}</h3>
                                    <label for="cancel-reason-{{ $order->id }}">Lý do hủy</label>
                                    <select id="cancel-reason-{{ $order->id }}" name="reason" onchange="toggleCancelReason(this, '{{ $order->id }}')">
                                        <option value="">Chọn lý do</option>
                                        <option @selected(old('reason') === 'Muốn thay đổi địa chỉ giao hàng')>Muốn thay đổi địa chỉ giao hàng</option>
                                        <option @selected(old('reason') === 'Muốn thay đổi món ăn/số lượng')>Muốn thay đổi món ăn/số lượng</option>
                                        <option @selected(old('reason') === 'Đổi ý không muốn mua nữa')>Đổi ý không muốn mua nữa</option>
                                        <option @selected(old('reason') === 'Tìm thấy giá tốt hơn')>Tìm thấy giá tốt hơn</option>
                                        <option @selected(old('reason') === 'Lý do khác')>Lý do khác</option>
                                    </select>
                                    <small id="cancel-reason-error-{{ $order->id }}" role="alert" style="display:block;color:#b42318">@error('reason'){{ $message }}@enderror</small>
                                    <label id="other-reason-wrap-{{ $order->id }}" style="display:none;margin-top:12px">Chi tiết lý do
                                        <textarea name="other_reason" maxlength="200" minlength="1" style="display:block;width:100%;margin-top:6px">{{ old('other_reason') }}</textarea>
                                    </label>
                                    <small id="other-reason-error-{{ $order->id }}" role="alert" style="display:block;color:#b42318">@error('other_reason'){{ $message }}@enderror</small>
                                    <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:18px">
                                        <button type="button" onclick="this.closest('dialog').close()">Hủy bỏ</button>
                                        <button type="submit">Xác nhận hủy</button>
                                    </div>
                                </form>
                            </dialog>
                        @endif
                    </div>

                    <div class="order-actions">
                        <div class="order-total">{{ number_format($order->total_amount, 0, ',', '.') }}đ</div>
                        <div class="action-buttons">
                            <a href="{{ route('checkout.success', ['id' => $order->id]) }}" class="btn-outline">Xem hóa đơn</a>

                            @if($order->status === 'completed')
                                @if($order->review || $order->reviewed_at)
                                    <span class="badge success">Đã đánh giá</span>
                                @else
                                    <a href="{{ route('orders.review', $order->id) }}" class="btn-outline">Đánh giá</a>
                                @endif
                            @endif
                            
                            <form action="{{ route('orders.reorder', $order->id) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn-primary">Đặt lại</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    function toggleCancelReason(select, orderId) {
        const wrapper = document.getElementById(`other-reason-wrap-${orderId}`);
        const field = wrapper.querySelector('textarea');
        const show = select.value === 'Lý do khác';
        wrapper.style.display = show ? 'block' : 'none';
        field.required = show;
    }

    document.querySelectorAll('.cancel-order-form').forEach(form => {
        form.addEventListener('submit', event => {
            const orderId = form.querySelector('[name="cancel_order_id"]').value;
            const reason = form.querySelector('[name="reason"]').value;
            const otherReason = form.querySelector('[name="other_reason"]').value;
            const reasonError = document.getElementById(`cancel-reason-error-${orderId}`);
            const otherReasonError = document.getElementById(`other-reason-error-${orderId}`);

            reasonError.textContent = reason ? '' : 'Vui lòng chọn lý do hủy đơn.';
            otherReasonError.textContent = '';

            if (!reason) {
                event.preventDefault();
                return;
            }

            if (reason === 'Lý do khác') {
                if (!otherReason.trim()) {
                    otherReasonError.textContent = 'Vui lòng nhập lý do hủy chi tiết.';
                    event.preventDefault();
                } else if (otherReason.length > 200) {
                    otherReasonError.textContent = 'Lý do hủy không được vượt quá 200 ký tự.';
                    event.preventDefault();
                }
            }
        });
    });

    const failedCancelOrderId = @json(old('cancel_order_id'));
    if (failedCancelOrderId) {
        const failedDialog = document.getElementById(`cancel-order-${failedCancelOrderId}`);
        if (failedDialog) {
            failedDialog.showModal();
            toggleCancelReason(failedDialog.querySelector('[name="reason"]'), failedCancelOrderId);
        }
    }
</script>
@endpush