<?php

namespace Tests\Unit;

use App\Models\Order;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class OrderStateLabelTest extends TestCase
{
    #[DataProvider('statusLabels')]
    public function test_order_statuses_have_the_expected_vietnamese_labels(string $status, string $expectedLabel): void
    {
        $order = new Order(['status' => $status]);

        $this->assertSame($expectedLabel, $order->getStateLabel());
    }

    public static function statusLabels(): array
    {
        return [
            'pending' => ['pending', 'Chờ xác nhận'],
            'confirmed' => ['confirmed', 'Đã xác nhận'],
            'preparing' => ['preparing', 'Đang chuẩn bị'],
            'delivering' => ['delivering', 'Đang giao'],
            'completed' => ['completed', 'Đã giao'],
            'cancelled' => ['cancelled', 'Đã hủy'],
        ];
    }
}