@extends('layouts.app')

@section('content')
<style>
    /* Ép toàn bộ giao diện hóa đơn tuân theo đúng 1 font Arial duy nhất */
    .receipt-page-bg * {
        font-family: Arial, Helvetica, sans-serif !important;
        box-sizing: border-box;
    }
    .receipt-page-bg {
        background-color: #f5f5f5;
        min-height: calc(100vh - 120px);
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 40px 15px;
    }
    .receipt-card-box {
        background: #ffffff;
        width: 100%;
        max-width: 520px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.06);
        border: 1px solid #eee;
        overflow: hidden;
    }
    .receipt-brand-header {
        text-align: center;
        padding: 30px 25px 20px;
        border-bottom: 2px dashed #f0f0f0;
    }
    .brand-logo-invoice {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 10px;
    }
    .brand-logo-invoice img {
        height: 42px;
        width: auto;
        object-fit: contain;
    }
    .brand-logo-invoice span {
        font-size: 24px;
        font-weight: bold;
        color: #ff5722;
    }
    .receipt-brand-header h3 {
        margin: 0 0 15px;
        font-size: 13px;
        font-weight: bold;
        color: #666;
        letter-spacing: 0.5px;
    }
    .receipt-info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
        padding: 25px;
        background: #fafafa;
        border-bottom: 1px solid #eee;
        font-size: 14px;
    }
    .info-grid-cell label {
        display: block;
        color: #888;
        font-size: 12px;
        font-weight: bold;
        margin-bottom: 4px;
    }
    .info-grid-cell span {
        color: #222;
        font-weight: bold;
    }
    .receipt-items-table {
        width: 100%;
        border-collapse: collapse;
        margin: 20px 0;
    }
    .receipt-items-table th {
        text-align: left;
        color: #888;
        font-size: 13px;
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
    }
    .receipt-items-table td {
        padding: 12px 0;
        border-bottom: 1px dashed #eee;
        font-size: 14px;
        color: #333;
    }
    .receipt-pricing-summary {
        background: #fff;
        padding: 0 25px 25px;
    }
    .pricing-summary-row {
        display: flex;
        justify-content: space-between;
        font-size: 14px;
        color: #555;
        margin-bottom: 12px;
    }
    .pricing-summary-row.final-grand-row {
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid #eee;
        font-size: 18px;
        font-weight: bold;
        color: #ff5722;
        margin-bottom: 25px;
    }
    .btn-return-home {
        display: block;
        background: #ff5722;
        color: #ffffff;
        text-align: center;
        text-decoration: none;
        padding: 14px;
        border-radius: 8px;
        font-weight: bold;
        font-size: 15px;
        transition: 0.3s;
    }
    .btn-return-home:hover {
        background-color: #e64a19;
    }
</style>

<div class="receipt-page-bg">
    <div class="receipt-card-box">
        <div class="receipt-brand-header">
            <div class="brand-logo-invoice">
                <img src="{{ asset('images/logo.png') }}" alt="MlemNow Logo">
                <span>MlemNow</span>
            </div>
            <h3>HÓA ĐƠN XÁC NHẬN ĐƠN HÀNG THÀNH CÔNG</h3>
            <span style="background: #e8f5e9; color: #2e7d32; padding: 6px 16px; border-radius: 20px; font-size: 12.5px; font-weight: bold;">
                {{ $order->getStateLabel() ?? 'Đang chuẩn bị món' }}
            </span>
        </div>

        <div class="receipt-info-grid">
            <div class="info-grid-cell">
                <label>Mã đơn hàng</label>
                <span style="color:#ff5722;">#{{ $order->order_code }}</span>
            </div>
            <div class="info-grid-cell">
                <label>Thời gian đặt</label>
                <span>{{ $order->created_at->format('H:i - d/m/Y') }}</span>
            </div>
            <div class="info-grid-cell">
                <label>Người nhận hàng</label>
                <span>{{ $order->customer_name }}</span>
            </div>
            <div class="info-grid-cell">
                <label>Số điện thoại</label>
                <span>{{ $order->phone }}</span>
            </div>
            <div class="info-grid-cell" style="grid-column: span 2;">
                <label>Địa chỉ giao món chi tiết</label>
                <span>{{ $order->delivery_address }}</span>
            </div>
            <div class="info-grid-cell" style="grid-column: span 2;">
                <label>Thời gian giao hàng ước tính</label>
                <span style="color:#2e7d32;">{{ $order->delivery_time ?? 'Giao ngay lập tức (15-30 phút)' }}</span>
            </div>
        </div>

        <div style="padding: 0 25px;">
            <table class="receipt-items-table">
                <thead>
                    <tr>
                        <th>Tên sản phẩm món ăn</th>
                        <th style="text-align: center; width: 40px;">SL</th>
                        <th style="text-align: right; width: 100px;">Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                    <tr>
                        <td><strong style="color:#222;">{{ $item->food_name }}</strong></td>
                        <td style="text-align: center; color: #666;">x{{ $item->quantity }}</td>
                        <td style="text-align: right; font-weight: bold; color:#222;">{{ number_format($item->price * $item->quantity, 0, ',', '.') }}đ</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="receipt-pricing-summary">
            <div class="pricing-summary-row">
                <span>Thành tiền phần ăn:</span>
                <span style="font-weight:bold; color:#222;">{{ number_format($order->subtotal, 0, ',', '.') }}đ</span>
            </div>
            <div class="pricing-summary-row">
                <span>Phí vận chuyển:</span>
                <span style="font-weight:bold; color:#222;">{{ number_format($order->delivery_fee ?? 20000, 0, ',', '.') }}đ</span>
            </div>
            
            @if($order->discount_applied > 0)
            <div class="pricing-summary-row" style="color: #ff5722;">
                <span>Khuyến mãi Voucher áp dụng:</span>
                <span style="font-weight:bold;">-{{ number_format($order->discount_applied, 0, ',', '.') }}đ</span>
            </div>
            @endif
            
            <div class="receipt-pricing-summary">
                <div class="pricing-summary-row final-grand-row">
                    <span>TỔNG ĐÃ THANH TOÁN:</span>
                    <span>{{ number_format($order->total_amount, 0, ',', '.') }}đ</span>
                </div>

                <form action="{{ route('order.export-pdf', $order->id) }}" method="POST" style="margin-bottom: 10px;">
                    @csrf
                    <button type="submit" 
                        style="width: 100%; padding: 14px; border: 1px solid #ff5722; background: #fff; color: #ff5722; border-radius: 8px; font-weight: bold; cursor: pointer; transition: 0.3s;">
                        <i class="fas fa-file-pdf"></i> Tải hóa đơn (PDF)
                    </button>
                </form>

                <a href="{{ route('home') }}" class="btn-return-home">Quay lại trang chủ</a>
            </div>
        </div>
    </div>
</div>
@endsection