<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('food_id')->nullable()->constrained()->nullOnDelete();
            $table->string('food_name'); // Lưu hardcode tên món lúc đặt để tránh lỗi hiển thị hóa đơn cũ nếu món bị đổi tên
            $table->string('size_name')->nullable();
            $table->integer('quantity');
            $table->integer('price'); // Giá của 1 sản phẩm tại thời điểm đặt (đã bao gồm tiền size)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
