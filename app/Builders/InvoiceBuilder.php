<?php

namespace App\Builders;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Order;

class InvoiceBuilder
{
    protected $order;
    protected $data = [];

    public function __construct(Order $order)
    {
        $this->order = $order;
        // Dữ liệu mặc định
        $this->data['order'] = $order;
    }

    // Bước 1: Thêm thông tin khách hàng (Có thể tùy chỉnh)
    public function addCustomerInfo()
    {
        $this->data['customer'] = [
            'name' => $this->order->customer_name,
            'phone' => $this->order->phone
        ];
        return $this; // Trả về $this để chaining (gọi chuỗi)
    }

    // Bước 2: Thêm chi tiết món ăn
    public function addFoodItems()
    {
        $this->data['items'] = $this->order->items;
        return $this;
    }

    // Bước 3: Áp dụng các tính toán thuế/giảm giá
    public function applyTaxes()
    {
        $this->data['vat'] = $this->order->subtotal * 0.08; // Ví dụ VAT 8%
        return $this;
    }

    // Bước cuối: Render ra PDF
    public function buildPdf()
    {
        $pdf = Pdf::loadView('invoices.template', $this->data);
        return $pdf;
    }
}