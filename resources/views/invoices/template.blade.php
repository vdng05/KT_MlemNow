<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Hóa đơn #{{ $order->order_code }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 14px; color: #333; }
        .invoice-card { width: 100%; max-width: 500px; margin: 0 auto; padding: 20px; }
        
        /* Header */
        .header { text-align: center; border-bottom: 2px dashed #f0f0f0; padding-bottom: 15px; margin-bottom: 20px; }
        .logo { color: #ff5722; font-size: 28px; font-weight: bold; }
        .title { font-size: 13px; font-weight: bold; color: #666; margin-top: 10px; }
        .badge { background: #e8f5e9; color: #2e7d32; padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; display: inline-block; margin-top: 10px; }

        /* Info Grid */
        .info-table { width: 100%; margin-bottom: 20px; font-size: 13px; background: #fafafa; padding: 10px; }
        .info-table td { padding: 5px; }
        .label { color: #888; font-weight: bold; font-size: 11px; }

        /* Table */
        .items-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .items-table th { text-align: left; color: #888; font-size: 12px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        .items-table td { padding: 10px 0; border-bottom: 1px dashed #eee; font-size: 14px; color: #333; }

        /* Summary */
        .summary-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .summary-table td { padding: 5px 0; color: #555; }
        .final-grand-row { border-top: 1px solid #eee; padding-top: 10px; margin-top: 10px; font-size: 16px; font-weight: bold; color: #ff5722; }
    </style>
</head>
<body>
    <div class="invoice-card">
        <div class="header">
            <div class="brand-logo-invoice">
                <img src="{{ public_path('images/logo.png') }}" style="height: 80px;">
            </div>
            <div class="title">HÓA ĐƠN XÁC NHẬN ĐƠN HÀNG THÀNH CÔNG</div>
        </div>

        <table class="info-table">
            <tr>
                <td style="width: 50%;"><div class="label">Mã đơn hàng</div><span style="color:#ff5722; font-weight:bold;">#{{ $order->order_code }}</span></td>
                <td><div class="label">Thời gian đặt</div><span>{{ $order->created_at->format('H:i - d/m/Y') }}</span></td>
            </tr>
            <tr>
                <td><div class="label">Người nhận hàng</div><span>{{ $order->customer_name }}</span></td>
                <td><div class="label">Số điện thoại</div><span>{{ $order->phone }}</span></td>
            </tr>
            <tr>
                <td colspan="2"><div class="label">Địa chỉ giao món chi tiết</div><span>{{ $order->delivery_address }}</span></td>
            </tr>
        </table>

        <table class="items-table">
            <thead>
                <tr>
                    <th>Tên sản phẩm món ăn</th>
                    <th style="text-align: center;">SL</th>
                    <th style="text-align: right;">Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                <tr>
                    <td><strong>{{ $item->food_name }}</strong></td>
                    <td style="text-align: center; color: #666;">x{{ $item->quantity }}</td>
                    <td style="text-align: right; font-weight: bold;">{{ number_format($item->price * $item->quantity, 0, ',', '.') }}đ</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <table class="summary-table">
            <tr>
                <td>Thành tiền phần ăn:</td>
                <td style="text-align: right;">{{ number_format($order->subtotal, 0, ',', '.') }}đ</td>
            </tr>
            <tr>
                <td>Phí vận chuyển:</td>
                <td style="text-align: right;">{{ number_format($order->delivery_fee ?? 20000, 0, ',', '.') }}đ</td>
            </tr>
            @if($order->discount_applied > 0)
            <tr style="color: #ff5722;">
                <td>Khuyến mãi Voucher:</td>
                <td style="text-align: right;">-{{ number_format($order->discount_applied, 0, ',', '.') }}đ</td>
            </tr>
            @endif
            <tr>
                <td colspan="2" class="final-grand-row">
                    <table style="width: 100%;">
                        <tr>
                            <td style="color: #ff5722;">TỔNG ĐÃ THANH TOÁN:</td>
                            <td style="text-align: right; color: #ff5722;">{{ number_format($order->total_amount, 0, ',', '.') }}đ</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>