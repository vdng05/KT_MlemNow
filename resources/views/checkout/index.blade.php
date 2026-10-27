@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<style>
    .checkout-body-wrapper * {
        font-family: Arial, Helvetica, sans-serif !important;
        box-sizing: border-box;
    }
    .checkout-body-wrapper {
        background-color: #f7f8fa;
        padding: 40px 0;
        min-height: calc(100vh - 120px);
    }
    .checkout-grid {
        display: flex;
        gap: 30px;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 15px;
    }
    .checkout-panel-left {
        width: 63%;
        background: #ffffff;
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
    }
    .checkout-panel-right {
        width: 37%;
        background: #ffffff;
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        position: sticky;
        top: 100px;
        height: max-content;
    }
    .panel-header-title {
        font-size: 18px;
        font-weight: bold;
        color: #111;
        margin-bottom: 25px;
        padding-bottom: 12px;
        border-bottom: 2px solid #ff5722;
    }
    .form-group-item { margin-bottom: 22px; }
    .form-group-item label.field-title {
        display: block;
        font-weight: bold;
        font-size: 14px;
        color: #333;
        margin-bottom: 8px;
    }
    .input-field-custom {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 14px;
        color: #333;
        transition: 0.3s;
    }
    .input-field-custom:focus { border-color: #ff5722; outline: none; }
    .validation-alert {
        color: #d32f2f;
        font-size: 13px;
        margin-top: 6px;
        display: none;
    }
    
    .custom-box-input { display: none; }
    .custom-box-label {
        display: block;
        padding: 15px;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        cursor: pointer;
        transition: 0.3s;
        background: #fff;
    }
    .custom-box-input:checked + .custom-box-label {
        border-color: #ff5722;
        background: #fff9f6;
    }
    .custom-box-label strong {
        display: block;
        font-size: 14px;
        color: #222;
        margin-bottom: 4px;
    }
    .custom-box-label p { font-size: 13px; color: #666; margin: 0; }

    .summary-items-list {
        margin-bottom: 20px;
        border-bottom: 1px solid #eee;
        padding-bottom: 10px;
    }
    .item-summary-card {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        padding: 12px 0;
        border-bottom: 1px dashed #f5f5f5;
    }
    .item-summary-left { max-width: 70%; }
    .item-summary-name {
        font-size: 15px;
        font-weight: bold;
        color: #111;
        margin-bottom: 4px;
    }
    .item-summary-details { font-size: 13px; color: #666; line-height: 1.4; }
    .item-summary-price {
        font-size: 15px;
        font-weight: bold;
        color: #333;
        text-align: right;
    }
    .price-breakdown-box {
        display: flex;
        flex-direction: column;
        gap: 12px;
        font-size: 14px;
        color: #555;
    }
    .price-flex-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 12px;
        font-size: 14px;
        color: #555;
    }
    .price-flex-row.grand-total {
        font-size: 19px;
        font-weight: bold;
        color: #ff5722;
        padding-top: 15px;
        border-top: 1px dashed #ddd;
        margin-top: 5px;
    }
    .btn-submit-checkout {
        width: 100%;
        background-color: #ff5722;
        color: #ffffff;
        border: none;
        padding: 15px;
        border-radius: 8px;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        transition: 0.3s;
    }
    .btn-submit-checkout:hover { background-color: #e64a19; }
    
    .btn-back-to-cart {
        display: block;
        width: 100%;
        text-align: center;
        background-color: transparent;
        color: #ff5722;
        border: 1px solid #ff5722;
        padding: 13px;
        border-radius: 8px;
        font-size: 15px;
        font-weight: bold;
        text-decoration: none;
        margin-top: 12px;
        transition: 0.3s;
    }
    .btn-back-to-cart:hover { background-color: #fff3ef; }

    .checkout-address-card {
        display: grid;
        grid-template-columns: 30px minmax(0, 1fr) auto;
        gap: 10px;
        align-items: center;
        padding: 15px;
        border: 1px solid #e7c8b6;
        border-radius: 9px;
        background: #fffaf7;
    }
    .checkout-address-pin { display: grid; width: 28px; height: 30px; place-items: center; color: #ed642d; font-size: 28px; line-height: 1; }
    .checkout-address-name { color: #34251f; font-weight: 700; }
    .checkout-address-phone { margin-left: 6px; color: #736158; }
    .checkout-address-text { margin: 5px 0 0; color: #66554c; line-height: 1.45; }
    .checkout-address-change {
        padding: 8px 12px;
        color: #bd4b1d;
        background: white;
        border: 1px solid #ed9b72;
        border-radius: 6px;
        font-weight: 700;
        white-space: nowrap;
        cursor: pointer;
    }
    .checkout-address-change:hover { background: #fff0e7; }
    .saved-address-dialog {
        width: min(600px, calc(100% - 32px));
        max-height: min(84vh, 700px);
        overflow: auto;
        padding: 24px;
        color: #34251f;
        background: #fffdfb;
        border: 1px solid #efc8b4;
        border-radius: 12px;
        box-shadow: 0 24px 80px rgba(40, 22, 14, .25);
    }
    .saved-address-dialog::backdrop { background: rgba(35, 24, 19, .56); }
    .saved-address-dialog h2 { margin: 0 0 16px; color: #ed642d; font-size: 21px; }
    .saved-address-options { display: grid; gap: 10px; }
    .saved-address-option { display: grid; grid-template-columns: 20px 25px minmax(0, 1fr); gap: 9px; align-items: start; padding: 13px; border: 1px solid #ddd4cf; border-radius: 8px; cursor: pointer; }
    .saved-address-option:has(input:checked) { border-color: #ed8d5f; background: #fff4ed; }
    .saved-address-option input { margin-top: 4px; accent-color: #ed642d; }
    .saved-address-option strong { color: #34251f; }
    .saved-address-option small { display: block; margin-top: 4px; color: #6c5a50; line-height: 1.45; }
    .saved-address-dialog-actions { display: flex; justify-content: space-between; align-items: center; gap: 10px; margin-top: 18px; }
    .saved-address-dialog-actions a { color: #bd4b1d; font-weight: 650; }
    .saved-address-dialog-actions button { padding: 10px 15px; color: white; background: #ed642d; border: 0; border-radius: 6px; font-weight: 700; cursor: pointer; }
    #savedAddressModal[open] { position: fixed; inset: 0; margin: auto; }
    @media (max-width: 560px) {
        .checkout-address-card { grid-template-columns: 24px minmax(0, 1fr); }
        .checkout-address-change { grid-column: 2; justify-self: end; }
        .saved-address-dialog { padding: 18px; }
        .saved-address-dialog-actions { align-items: flex-start; flex-direction: column; }
    }

</style>

<div class="checkout-body-wrapper">
    <div class="checkout-grid">
        <div class="checkout-panel-left">
            <div class="panel-header-title">📦 THÔNG TIN GIAO HÀNG</div>
            <!-- @if(!empty($isBuyNow))
                <div style="background: #fff8e1; border: 1px solid #ffe082; color: #b78103; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 18px;">⚡</span>
                    <span>Bạn đang đặt hàng ở chế độ <strong>Mua ngay</strong> (Chỉ thanh toán 1 món đã chọn, các món trong giỏ hàng vẫn được giữ nguyên).</span>
                </div>
            @endif -->
            @if($errors->any())
                <div role="alert" style="color:#b42318;margin-bottom:16px">{{ $errors->first() }}</div>
            @endif
            
            <form action="{{ route('checkout.process') }}" method="POST" id="mainCheckoutForm" novalidate onsubmit="return handleFormValidation()">
                @csrf
                
                <div class="form-group-item">
                    <label class="field-title">Họ và tên người nhận <span style="color:red">*</span></label>
                    <input type="text" name="name" id="checkName" class="input-field-custom" value="{{ auth()->user()->name ?? '' }}" required minlength="2" maxlength="50" pattern="[A-Za-zÀ-ỹ ]+">
                    <div class="validation-alert" id="errName">Vui lòng thêm thông tin người nhận.</div>
                </div>

                <div class="form-group-item">
                    <label class="field-title">Số điện thoại <span style="color:red">*</span></label>
                    <input type="text" name="phone" id="checkPhone" class="input-field-custom" value="{{ auth()->user()->phone ?? '' }}" required maxlength="10" pattern="0[0-9]{9}">
                    <div class="validation-alert" id="errPhone">Số điện thoại phải gồm đúng 10 chữ số và bắt đầu bằng số 0.</div>
                </div>

                <div class="form-group-item">
                    <label class="field-title">Địa chỉ nhận hàng <span style="color:red">*</span></label>
                    @php($selectedAddress = $savedAddresses->firstWhere('is_default', true) ?? $savedAddresses->first())
                    <div class="checkout-address-card" id="checkoutAddressCard">
                        <span class="checkout-address-pin" aria-hidden="true">⌂</span>
                        <div>
                            <div class="checkout-address-name" id="checkoutAddressName">{{ $selectedAddress?->recipient_name ?? auth()->user()->name }}</div>
                            <span class="checkout-address-phone" id="checkoutAddressPhone">{{ $selectedAddress?->phone ?? auth()->user()->phone }}</span>
                            <p class="checkout-address-text" id="checkoutAddressText">{{ $selectedAddress?->address ?? 'Chưa có địa chỉ được lưu' }}</p>
                        </div>
                        <button type="button" class="checkout-address-change" onclick="openSavedAddressPopup()">Chọn một địa chỉ khác</button>
                    </div>
                    <input type="hidden" name="address" id="realAddressInput" value="{{ $selectedAddress?->address }}" required>
                    <div class="validation-alert" id="errAddress">Vui lòng thêm địa chỉ nhận hàng.</div>
                </div>

                <div class="form-group-item">
                    <label class="field-title">Thời gian nhận hàng <span style="color:red">*</span></label>
                    <div style="display: flex; gap: 15px;">
                        <div style="flex: 1;">
                            <input type="radio" name="time_select" id="time_now" value="Giao ngay" checked class="custom-box-input" onclick="setDeliveryTimeType('Giao ngay')">
                            <label for="time_now" class="custom-box-label" style="text-align: center;">
                                <strong>Giao ngay lập tức</strong>
                                <p>(Ước tính 15-30 phút)</p>
                            </label>
                        </div>
                        <div style="flex: 1;">
                            <input type="radio" name="time_select" id="time_later" value="Hẹn giờ" class="custom-box-input" onclick="openSchedulePopup()">
                            <label for="time_later" class="custom-box-label" style="text-align: center;">
                                <strong>Đặt lịch hẹn giờ</strong>
                                <p>Tùy chọn mốc thời gian nhận</p>
                            </label>
                        </div>
                    </div>
                    <input type="hidden" name="time_type" id="realTimeInput" value="Giao ngay">
                    <div id="timeNoticeLabel" style="margin-top: 12px; color: #ff5722; font-weight: bold; font-size: 13.5px; display: none;"></div>
                </div>

                <div class="form-group-item" style="background: #fffaf7; padding: 20px; border-radius: 8px; border: 1px dashed #ff5722;">
                    <label class="field-title" style="color: #ff5722;">🎟️ Mã giảm giá / Voucher</label>
                    <div style="display:flex; gap:10px; margin-top: 8px;">
                        <input type="text" id="vouchCode" name="voucher_code" placeholder="Nhập mã giảm giá..." class="input-field-custom" style="text-transform:uppercase; background: #fff; flex: 1; width: auto;">
                        
                        <button type="button" onclick="triggerVoucherCheck()" style="background:#ff5722; color:white; border:none; padding:0 22px; border-radius:8px; font-weight:bold; cursor:pointer; white-space: nowrap;">Áp dụng</button>
                    </div>
                    <div style="margin-top: 10px;">
                        <a href="javascript:void(0)" onclick="openVoucherModal()" style="color: #ff5722; font-size: 13.5px; font-weight: bold; text-decoration: underline; display: inline-flex; align-items: center; gap: 6px; cursor: pointer;">
                            <i class="fas fa-ticket-alt"></i> <span>Mã khả dụng</span>
                        </a>
                    </div>
                    <div id="vouchAlertStatus" style="margin-top: 10px; font-size: 13px; font-weight: bold; display: none;"></div>
                </div>

                <div class="form-group-item">
                    <label class="field-title">Ghi chú gửi nhà hàng & tài xế</label>
                    <textarea id="orderNote" name="order_note" rows="3" class="input-field-custom" style="resize: none;" minlength="5" maxlength="200" placeholder="Ghi chú chi tiết giao hàng tại đây..."></textarea>
                    <div class="validation-alert" id="errOrderNote"></div>
                </div>
        </div>

        <div class="checkout-panel-right">
            <div class="panel-header-title">
                🧾 TÓM TẮT ĐƠN HÀNG
                @if(!empty($isBuyNow))
                    <span style="font-size: 13px; color: #ff5722; font-weight: normal; margin-left: 6px;">(Mua ngay)</span>
                @endif
            </div>
            
            <div class="summary-items-list">
                @foreach($cart as $item)
                <div class="item-summary-card">
                    <div class="item-summary-left">
                        <div class="item-summary-name">{{ $item['base_name'] ?? $item['name'] }}</div>
                        <div class="item-summary-details">
                            Phân loại: {{ $item['size'] ?? 'Tiêu chuẩn' }} <br>
                            @if(!empty($item['toppings']))
                                Topping: {{ $item['toppings'] }}
                            @endif
                            @if(!empty($item['note']))
                                <div style="color: #888; font-style: italic; margin-top: 2px;">Món ăn: {{ $item['note'] }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="item-summary-price">
                        <div style="font-size: 12px; color: #777; font-weight: normal;">x{{ $item['quantity'] }}</div>
                        <div>{{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}đ</div>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="price-breakdown-box">
                <div class="price-flex-row">
                    <span>Thành tiền phần ăn:</span>
                    <span style="color:#111; font-weight: bold;">{{ number_format($subtotal, 0, ',', '.') }}đ</span>
                </div>
                <div class="price-flex-row">
                    <span>Phí vận chuyển:</span>
                    <span style="color:#111; font-weight: bold;">{{ number_format($shippingFee, 0, ',', '.') }}đ</span>
                </div>
                <div class="price-flex-row" id="vouchDiscountRow" style="color: #ff5722; display: none;">
                    <span>Khuyến mãi Voucher:</span>
                    <span style="font-weight: bold;" id="vouchDiscountValue">-0đ</span>
                </div>
                <div class="price-flex-row grand-total">
                    <span>Tổng thanh toán:</span>
                    <span id="grandTotalLabel" data-base="{{ $subtotal + $shippingFee }}">{{ number_format($subtotal + $shippingFee, 0, ',', '.') }}đ</span>
                </div>
            </div>

            <button type="submit" class="btn-submit-checkout" style="margin-top: 25px;">Xác nhận đặt hàng</button>
            <a href="{{ route('cart.index') }}" class="btn-back-to-cart">Quay lại giỏ hàng</a>
            </form>
        </div>
    </div>
</div>

<div id="mapModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:#fff; padding:25px; border-radius:12px; width:600px; box-shadow: 0 4px 20px rgba(0,0,0,0.15);">
        <h3 style="margin-bottom:15px; font-size:16px; font-weight:bold;"><i class="fas fa-map-marker-alt" style="color:#ff5722"></i> Chọn vị trí nhận đồ trên bản đồ</h3>
        
        <div style="display:flex; gap:10px; margin-bottom:12px;">
            <input type="text" id="mapSearchInput" placeholder="Tìm tên đường, tòa nhà... (VD: Đại học Thủy Lợi)" class="input-field-custom">
            <button type="button" onclick="searchMapLocation()" style="background:#333; color:#fff; border:none; padding:0 18px; border-radius:8px; font-weight:bold; cursor:pointer;">Tìm kiếm</button>
        </div>
        
        <div id="realMapContainer" style="width:100%; height:280px; border-radius:8px; margin-bottom:15px; border: 1px solid #ddd; z-index: 1;"></div>
        
        <input type="text" id="customAddrInput" placeholder="Địa chỉ chi tiết nhận hàng chính thức..." class="input-field-custom" style="margin-bottom:15px;">
        
        <div style="display:flex; justify-content:flex-end; gap:10px;">
            <button type="button" onclick="closeMapPopupBox('cancel')" style="padding:10px 18px; border:1px solid #ccc; border-radius:6px; background:#fff; cursor:pointer; font-size: 14px;">Hủy</button>
            <button type="button" onclick="closeMapPopupBox('save')" style="padding:10px 18px; background:#ff5722; color:white; border:none; border-radius:6px; font-weight:bold; cursor:pointer; font-size: 14px;">Xác nhận vị trí</button>
        </div>
    </div>
</div>

<dialog class="saved-address-dialog" id="savedAddressModal">
    <h2>Địa chỉ đã lưu</h2>
    @if($savedAddresses->isNotEmpty())
        <div class="saved-address-options">
            @foreach($savedAddresses as $address)
                <label class="saved-address-option">
                    <input type="radio" name="saved_address_choice" value="{{ $address->id }}" data-address="{{ $address->address }}" data-name="{{ $address->recipient_name ?? auth()->user()->name }}" data-phone="{{ $address->phone ?? auth()->user()->phone }}" @checked($selectedAddress && $selectedAddress->id === $address->id)>
                    <span class="checkout-address-pin" aria-hidden="true">⌂</span>
                    <span><strong>{{ $address->recipient_name ?? auth()->user()->name }} · {{ $address->phone ?? auth()->user()->phone }}</strong><small>{{ $address->address }}{{ $address->is_default ? ' · Mặc định' : '' }}</small></span>
                </label>
            @endforeach
        </div>
    @else
        <p>Bạn chưa lưu địa chỉ giao hàng.</p>
    @endif
    <div class="saved-address-dialog-actions">
        <a href="{{ route('account.addresses.index') }}">Quản lý địa chỉ</a>
        <div style="display:flex;gap:8px">
            <button type="button" style="color:#6c5a50;background:white;border:1px solid #ddd4cf" onclick="document.getElementById('savedAddressModal').close()">Đóng</button>
            @if($savedAddresses->isNotEmpty())
                <button type="button" onclick="applySavedAddress()">Chọn địa chỉ</button>
            @endif
        </div>
    </div>
</dialog>

<div id="timeModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:#fff; padding:25px; border-radius:12px; width:360px;">
        <h3 style="margin-bottom:15px; font-size:16px; font-weight:bold;"><i class="far fa-clock" style="color:#ff5722"></i> Nhập thời gian nhận hàng</h3>
        <label style="font-size: 13px; color: #555; margin-bottom: 6px; display: block;">Chọn ngày và giờ chính xác muốn nhận đồ:</label>
        <input type="datetime-local" id="timeInputPicker" class="input-field-custom" style="margin-bottom:20px;">
        <div style="display:flex; justify-content:flex-end; gap:10px;">
            <button type="button" onclick="closeTimePopupBox('cancel')" style="padding:10px 15px; border:1px solid #ccc; border-radius:6px; background:#fff; cursor:pointer; font-size: 14px;">Hủy</button>
            <button type="button" onclick="closeTimePopupBox('save')" style="padding:10px 15px; background:#ff5722; color:white; border:none; border-radius:6px; font-weight:bold; cursor:pointer; font-size: 14px;">Xác nhận mốc giờ</button>
        </div>
    </div>
</div>

<div id="voucherModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.55); z-index:9999; align-items:center; justify-content:center; padding: 15px;">
    <div style="background:#fff; border-radius:14px; width:620px; max-width: 100%; max-height: 85vh; display: flex; flex-direction: column; box-shadow: 0 10px 30px rgba(0,0,0,0.2); overflow: hidden;">
        <div style="padding: 18px 22px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; background: #fffaf7;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 20px;">🎟️</span>
                <h3 style="margin: 0; font-size: 17px; font-weight: bold; color: #333;">Danh sách mã khả dụng</h3>
            </div>
            <button type="button" onclick="closeVoucherModal()" style="background: none; border: none; font-size: 24px; line-height: 1; color: #888; cursor: pointer; padding: 0 5px;">&times;</button>
        </div>

        <div style="padding: 20px; overflow-y: auto; display: flex; flex-direction: column; gap: 14px;">
            @if(isset($availableVouchers) && $availableVouchers->isNotEmpty())
                @foreach($availableVouchers as $voucher)
                    <div class="voucher-card" style="border: 1px solid #ffd3c2; border-radius: 10px; padding: 14px 16px; background: #fffcfb; display: flex; justify-content: space-between; align-items: center; gap: 14px; transition: 0.2s; box-shadow: 0 2px 8px rgba(255,87,34,0.04);">
                        <div style="flex: 1;">
                            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                                <span style="display: inline-block; background: #ff5722; color: #fff; font-weight: bold; font-size: 13px; padding: 3px 8px; border-radius: 6px; letter-spacing: 0.5px;">{{ $voucher->code }}</span>
                                @if($voucher->isBirthday())
                                    <span style="background: #e91e63; color: white; font-size: 11px; padding: 2px 7px; border-radius: 10px; font-weight: bold;">🎂 Sinh nhật</span>
                                @endif
                                @if($voucher->quantity <= 0)
                                    <span style="background: #9e9e9e; color: white; font-size: 11px; padding: 2px 7px; border-radius: 10px;">Hết lượt</span>
                                @endif
                            </div>

                            <div style="font-size: 14.5px; font-weight: bold; color: #222; margin-bottom: 4px;">
                                @if($voucher->isBirthday())
                                    Ưu đãi sinh nhật: Giảm 50.000đ
                                @elseif($voucher->type === 'percent')
                                    Giảm {{ $voucher->discount_value }}% tổng giá trị đơn hàng
                                    @if($voucher->max_discount)
                                        <small style="color: #666; font-weight: normal;">(Tối đa {{ number_format($voucher->max_discount, 0, ',', '.') }}đ)</small>
                                    @endif
                                @else
                                    Giảm thẳng {{ number_format($voucher->discount_value, 0, ',', '.') }}đ
                                @endif
                            </div>

                            <div style="font-size: 12.5px; color: #666; line-height: 1.45;">
                                <span>• Đơn tối thiểu: <strong>{{ number_format($voucher->min_order_value ?: 100000, 0, ',', '.') }}đ</strong></span>
                                @if($voucher->isBirthday())
                                    <br><span>• Áp dụng trong 7 ngày liên tiếp kể từ ngày sinh nhật trong hồ sơ.</span>
                                @endif
                                @if($voucher->starts_at && $voucher->starts_at->isFuture())
                                    <br><span style="color: #d32f2f;">• Bắt đầu áp dụng từ: {{ $voucher->starts_at->format('d/m/Y') }}</span>
                                @elseif($voucher->expires_at)
                                    <br><span>• Hạn sử dụng: {{ $voucher->expires_at->format('d/m/Y') }}</span>
                                @endif
                            </div>
                        </div>

                        <div>
                            <button type="button" onclick="applyVoucherFromModal('{{ $voucher->code }}')" 
                                style="background: #ff5722; color: #fff; border: none; padding: 9px 18px; border-radius: 8px; font-size: 13.5px; font-weight: bold; cursor: pointer; white-space: nowrap; transition: 0.2s;">
                                Áp dụng
                            </button>
                        </div>
                    </div>
                @endforeach
            @else
                <div style="text-align: center; padding: 30px; color: #888;">
                    <p>Hiện không có mã giảm giá nào trong hệ thống.</p>
                </div>
            @endif
        </div>

        <div style="padding: 14px 20px; border-top: 1px solid #eee; display: flex; justify-content: flex-end; background: #fafafa;">
            <button type="button" onclick="closeVoucherModal()" style="padding: 9px 20px; border: 1px solid #ccc; border-radius: 6px; background: #fff; cursor: pointer; font-size: 13.5px; font-weight: 500;">Đóng</button>
        </div>
    </div>
</div>

<div id="customAlertModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:10000; align-items:center; justify-content:center;">
    <div style="background:#fff; padding:30px; border-radius:12px; width:350px; text-align:center; box-shadow: 0 5px 20px rgba(0,0,0,0.15);">
        <div id="customAlertIcon"></div>
        <h3 style="margin-bottom:10px; font-size:18px; font-weight:bold; color:#333;" id="customAlertTitle">Thông báo</h3>
        <p style="font-size: 14.5px; color: #555; margin-bottom: 25px; line-height: 1.5;" id="customAlertMessage"></p>
        <button type="button" onclick="closeCustomAlert()" style="background:#ff5722; color:white; border:none; padding:12px 30px; border-radius:8px; font-weight:bold; cursor:pointer; font-size: 15px; width: 100%;">Đóng</button>
    </div>
</div>

<script>
    /* ----- HÀM ĐIỀU KHIỂN VOUCHER POPUP MODAL ----- */
    function openVoucherModal() {
        const modal = document.getElementById('voucherModal');
        if (modal) modal.style.display = 'flex';
    }

    function closeVoucherModal() {
        const modal = document.getElementById('voucherModal');
        if (modal) modal.style.display = 'none';
    }

    function applyVoucherFromModal(code) {
        document.getElementById('vouchCode').value = code;
        closeVoucherModal();
        triggerVoucherCheck();
    }

    window.addEventListener('click', function(e) {
        const voucherModal = document.getElementById('voucherModal');
        if (e.target === voucherModal) {
            closeVoucherModal();
        }
    });

    /* ----- HÀM ĐIỀU KHIỂN CUSTOM ALERT ----- */
    function showCustomAlert(message, type = 'warning') {
        const modal = document.getElementById('customAlertModal');
        const iconContainer = document.getElementById('customAlertIcon');
        const title = document.getElementById('customAlertTitle');
        const msg = document.getElementById('customAlertMessage');

        msg.innerText = message;

        if (type === 'success') {
            iconContainer.innerHTML = '<i class="fas fa-check-circle" style="color: #28a745; font-size: 50px; margin-bottom: 15px;"></i>';
            title.innerText = 'Thành công!';
        } else if (type === 'error') {
            iconContainer.innerHTML = '<i class="fas fa-times-circle" style="color: #dc3545; font-size: 50px; margin-bottom: 15px;"></i>';
            title.innerText = 'Đã có lỗi xảy ra!';
        } else {
            iconContainer.innerHTML = '<i class="fas fa-exclamation-triangle" style="color: #ff5722; font-size: 50px; margin-bottom: 15px;"></i>';
            title.innerText = 'Thông báo';
        }

        modal.style.display = 'flex';
    }

    function closeCustomAlert() {
        document.getElementById('customAlertModal').style.display = 'none';
    }


    /* ----- LOGIC BẢN ĐỒ & THANH TOÁN ----- */
    let realMap, activeMarker;

    function openMapPopup() { 
        document.getElementById('mapModal').style.display = 'flex';
        
        setTimeout(() => {
            if (!realMap) {
                const defaultLatLng = [21.0074, 105.8248]; 
                realMap = L.map('realMapContainer').setView(defaultLatLng, 15);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(realMap);
                
                activeMarker = L.marker(defaultLatLng, {draggable: true}).addTo(realMap);

                function reverseGeocode(lat, lng) {
                    fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
                        .then(res => res.json())
                        .then(data => {
                            if(data && data.display_name) {
                                document.getElementById('customAddrInput').value = data.display_name;
                            }
                        });
                }

                activeMarker.on('dragend', function() {
                    let pos = activeMarker.getLatLng();
                    reverseGeocode(pos.lat, pos.lng);
                });

                realMap.on('click', function(e) {
                    activeMarker.setLatLng(e.latlng);
                    reverseGeocode(e.latlng.lat, e.latlng.lng);
                });
            }
            realMap.invalidateSize();
        }, 200);
    }

    function searchMapLocation() {
        let query = document.getElementById('mapSearchInput').value.trim();
        if(!query) return;
        fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${query}`)
            .then(res => res.json())
            .then(data => {
                if(data && data.length > 0) {
                    let newPos = [data[0].lat, data[0].lon];
                    realMap.setView(newPos, 16);
                    activeMarker.setLatLng(newPos);
                    document.getElementById('customAddrInput').value = data[0].display_name;
                } else {
                    showCustomAlert("Không tìm thấy địa điểm này. Vui lòng thử từ khóa khác.", "warning");
                }
            });
    }

    function syncAddressToForm() {
        const selected = document.querySelector('input[name="saved_address_choice"]:checked');
        document.getElementById('realAddressInput').value = selected ? selected.dataset.address : '';
    }

    function openSavedAddressPopup() {
        document.getElementById('savedAddressModal').showModal();
    }

    function applySavedAddress() {
        const selected = document.querySelector('input[name="saved_address_choice"]:checked');
        if (!selected) return;
        document.getElementById('realAddressInput').value = selected.dataset.address;
        document.getElementById('checkoutAddressName').textContent = selected.dataset.name;
        document.getElementById('checkoutAddressPhone').textContent = selected.dataset.phone;
        document.getElementById('checkoutAddressText').textContent = selected.dataset.address;
        document.getElementById('savedAddressModal').close();
    }

    window.addEventListener('DOMContentLoaded', () => {
        syncAddressToForm();
    });

    function closeMapPopupBox(action) {
        if (action === 'save') {
            let fullAddress = document.getElementById('customAddrInput').value.trim();

            if (!fullAddress) {
                showCustomAlert('Vui lòng điền hoặc chọn địa chỉ nhận hàng chi tiết trên bản đồ!', "warning");
                return;
            }

            document.getElementById('mapModal').style.display = 'none';

            fetch('{{ route("user.update_address") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ address: fullAddress })
            })
            .then(response => response.json().then(data => ({ response, data })))
            .then(({ response, data }) => {
                if (!response.ok) {
                    showCustomAlert(data.message || 'Không thể lưu địa chỉ. Vui lòng thử lại.', 'error');
                    return;
                }
                if (data.success) {
                    let selectBox = document.getElementById('savedAddressSelect');
                    if (selectBox) {
                        let newOption = document.createElement('option');
                        newOption.value = data.address;
                        newOption.text = data.address;
                        
                        selectBox.add(newOption);
                        selectBox.value = data.address; 
                        
                        syncAddressToForm();
                    }
                    showCustomAlert('Đã thêm và lưu địa chỉ mới vào danh sách thành công!', "success");
                } else {
                    showCustomAlert('Không thể lưu địa chỉ vào cơ sở dữ liệu. Vui lòng thử lại.', "error");
                }
            })
            .catch(error => {
                console.error('Lỗi:', error);
                showCustomAlert('Có lỗi xảy ra khi đồng bộ địa chỉ.', "error");
            });

        } else {
            document.getElementById('mapModal').style.display = 'none';
        }
    }

    function openSchedulePopup() { document.getElementById('timeModal').style.display = 'flex'; }
    function closeTimePopupBox(action) {
        if(action === 'save') {
            let selected = document.getElementById('timeInputPicker').value;
            if(!selected) { 
                showCustomAlert('Vui lòng lựa chọn thời gian giao hàng.', "warning"); 
                return; 
            }
            let dateObj = new Date(selected);
            let formattedTime = dateObj.getHours() + ':' + (dateObj.getMinutes()<10?'0':'') + dateObj.getMinutes() + ' ngày ' + dateObj.getDate() + '/' + (dateObj.getMonth()+1);
            document.getElementById('realTimeInput').value = "Hẹn giờ: " + formattedTime;
            document.getElementById('timeNoticeLabel').style.display = "block";
            document.getElementById('timeNoticeLabel').innerText = "⏱️ Thời gian đặt lịch nhận: " + formattedTime;
        } else {
            document.getElementById('time_now').checked = true;
            document.getElementById('realTimeInput').value = "Giao ngay";
            document.getElementById('timeNoticeLabel').style.display = "none";
        }
        document.getElementById('timeModal').style.display = 'none';
    }
    
    function setDeliveryTimeType() {
        document.getElementById('realTimeInput').value = "Giao ngay";
        document.getElementById('timeNoticeLabel').style.display = "none";
    }

    function toggleAddressField(mode) {
        if(mode === 'saved') {
            document.getElementById('realAddressInput').value = "{{ auth()->user()->address ?? '' }}";
        }
    }

    function triggerVoucherCheck() {
        const code = document.getElementById('vouchCode').value.trim().toUpperCase();
        const alertBox = document.getElementById('vouchAlertStatus');
        const row = document.getElementById('vouchDiscountRow');
        const valLabel = document.getElementById('vouchDiscountValue');
        const totalLabel = document.getElementById('grandTotalLabel');
        const subtotal = {{ $subtotal }};
        const shippingFee = {{ $shippingFee }};
        const baseTotal = subtotal + shippingFee;

        alertBox.style.display = 'block';

        if(!code) { 
            alertBox.style.color = '#d32f2f'; 
            alertBox.innerText = 'Vui lòng nhập mã voucher.'; 
            return; 
        }

        fetch('{{ route("checkout.check_voucher") }}', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json', 
                'X-CSRF-TOKEN': '{{ csrf_token() }}' 
            },
            // Gửi mảng JSON có tên key trùng khít với $request->input() bên PHP
            body: JSON.stringify({ 
                code: code, 
                subtotal: subtotal 
            })
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                alertBox.style.color = '#2e7d32';
                alertBox.innerText = data.message;
                row.style.display = 'flex';
                valLabel.innerText = '-' + new Intl.NumberFormat('vi-VN').format(data.discount_amount) + 'đ';
                
                totalLabel.innerText = new Intl.NumberFormat('vi-VN').format(data.final_total) + 'đ';
            } else {
                alertBox.style.color = '#d32f2f';
                alertBox.innerText = data.message;
                row.style.display = 'none';
                totalLabel.innerText = new Intl.NumberFormat('vi-VN').format(baseTotal) + 'đ';
            }
        })
        .catch(error => {
            console.error('Lỗi kết nối API:', error);
            alertBox.style.color = '#d32f2f';
            alertBox.innerText = 'Đã xảy ra lỗi, vui lòng thử lại sau';
        });
    }

    function handleFormValidation() {
        const name = document.getElementById('checkName').value.trim();
        const phone = document.getElementById('checkPhone').value.trim();
        const address = document.getElementById('realAddressInput').value.trim();
        const timeType = document.getElementById('realTimeInput').value.trim();
        const orderNote = document.getElementById('orderNote').value.trim();
        const namePattern = /^[\p{L} ]+$/u;
        const phonePattern = /^0[0-9]{9}$/;
        const notePattern = /^[\p{L}\p{N} .,-]+$/u;

        let nameMessage = '';
        if (!name) nameMessage = 'Vui lòng thêm thông tin người nhận.';
        else if (/\p{N}/u.test(name)) nameMessage = 'Họ tên không được chứa chữ số.';
        else if (!namePattern.test(name)) nameMessage = 'Họ tên không được chứa ký tự đặc biệt.';
        else if (name.length < 2 || name.length > 50) nameMessage = 'Họ tên phải có độ dài từ 2-50 ký tự.';
        const nameError = document.getElementById('errName');
        nameError.textContent = nameMessage;
        nameError.style.display = nameMessage ? 'block' : 'none';

        const phoneError = document.getElementById('errPhone');
        phoneError.style.display = phonePattern.test(phone) ? 'none' : 'block';

        const addressError = document.getElementById('errAddress');
        addressError.style.display = !address || address === 'Chưa có địa chỉ được lưu' ? 'block' : 'none';

        if (!timeType) {
            showCustomAlert('Vui lòng lựa chọn thời gian giao hàng.', 'warning');
            return false;
        }

        let noteMessage = '';
        if (orderNote && (orderNote.length < 5 || orderNote.length > 200)) {
            noteMessage = orderNote.length > 200
                ? 'Ghi chú không được vượt quá 200 ký tự.'
                : 'Ghi chú có độ dài từ 5-200 ký tự.';
        } else if (orderNote && !notePattern.test(orderNote)) {
            noteMessage = 'Mô tả chỉ chứa chữ cái, chữ số, dấu chấm, dấu phẩy, dấu gạch ngang và khoảng trắng.';
        }
        const noteError = document.getElementById('errOrderNote');
        noteError.textContent = noteMessage;
        noteError.style.display = noteMessage ? 'block' : 'none';

        return !nameMessage && phonePattern.test(phone) && !!address && address !== 'Chưa có địa chỉ được lưu' && !noteMessage;
    }
</script>
@endsection