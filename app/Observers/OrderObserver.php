<?php

namespace App\Observers;

use App\Models\Order;

class OrderObserver
{
    /**
     * Bắt sự kiện khi tạo mới đơn hàng (Phục vụ cho việc chạy Seeder)
     */
    public function created(Order $order): void
    {
        // Nếu tạo mới mà trạng thái đã là completed (VD: test bằng Seeder)
        if ($order->status === 'completed') {
            $this->applyRewards($order);
        }
    }

    /**
     * Bắt sự kiện khi cập nhật đơn hàng (Phục vụ luồng chạy thực tế)
     */
    public function updated(Order $order): void
    {
        // SỬA TẠI ĐÂY: Dùng wasChanged thay cho isDirty
        if ($order->wasChanged('status') && $order->status === 'completed') {
            $this->applyRewards($order);
        }
    }

    /**
     * Tách riêng logic xử lý để tái sử dụng, giúp code sạch và dễ bảo trì
     */
    private function applyRewards(Order $order): void
    {
        // 1. Tự động cộng điểm tích lũy cho người dùng (1% giá trị đơn)
        // Dùng $order->user để đảm bảo relation tồn tại, tránh lỗi báo null
        if ($order->user_id && $order->user) {
            $pointsEarned = floor($order->total_amount / 100); 
            $order->user->increment('reward_points', $pointsEarned);
        }

        // 2. Tự động tăng lượt bán (sold_count) cho từng món ăn
        if ($order->items) {
            foreach ($order->items as $item) {
                // Dùng $item->food thay vì $item->food_id để chắc chắn Model Food tồn tại
                if ($item->food) {
                    $item->food->increment('sold_count', $item->quantity);
                }
            }
        }
    }

    public function deleted(Order $order): void {}
    public function restored(Order $order): void {}
    public function forceDeleted(Order $order): void {}
}