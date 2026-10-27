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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_code')->unique(); // Mã đơn hàng (VD: #FD2026VN)
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('voucher_id')->nullable()->constrained()->nullOnDelete();
            $table->string('customer_name');
            $table->string('phone');
            $table->string('delivery_address');
            $table->string('delivery_time')->nullable(); // Giao ngay hoặc hẹn giờ
            $table->string('payment_method'); // Tiền mặt (COD), Thẻ tín dụng, Ví điện tử
            $table->integer('subtotal'); // Tạm tính
            $table->integer('delivery_fee')->default(20000);
            $table->integer('discount_applied')->default(0); // Số tiền được giảm
            $table->integer('total_amount'); // Tổng cộng
            $table->text('note')->nullable(); // Ghi chú cho tài xế/quán
            $table->enum('status', ['pending', 'preparing', 'delivering', 'completed', 'cancelled'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
