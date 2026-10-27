<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class CleanAbandonedCarts extends Command
{
    // Tên lệnh để gõ trên terminal
    protected $signature = 'cart:clean';

    // Mô tả lệnh
    protected $description = 'Dọn dẹp các file session giỏ hàng quá 24h trong storage';

    public function handle()
    {
        $this->info("Bắt đầu quét thư mục session...");

        // Đường dẫn đến thư mục chứa session file
        $path = storage_path('framework/sessions');
        
        // Lấy tất cả file trong thư mục đó
        $files = File::files($path);
        $count = 0;
        
        // Thời điểm 24h trước
        $threshold = Carbon::now()->subHours(24)->timestamp;

        foreach ($files as $file) {
            // Kiểm tra xem file có phải là session không và thời gian sửa đổi cuối cùng có quá 24h không
            if (File::lastModified($file) < $threshold) {
                File::delete($file);
                $count++;
            }
        }

        $this->info("✓ Đã dọn dẹp xong. Tổng cộng {$count} file session cũ đã bị xóa.");
    }
}