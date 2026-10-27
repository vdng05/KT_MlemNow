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
        Schema::create('vouchers', function (Blueprint $table) {
        $table->id();
        $table->string('code')->unique(); // Mã voucher (VD: AJC18S36)
        
        // CÁC CỘT MỚI PHỤC VỤ FACTORY PATTERN
        $table->string('type')->default('amount'); // Loại giảm giá: 'percent' hoặc 'amount'
        $table->integer('discount_value'); // Giá trị giảm (% hoặc VNĐ)
        $table->integer('max_discount')->nullable(); // Mức giảm tối đa cho loại percent
        $table->integer('quantity')->default(100); // Số lượng lượt sử dụng
        
        $table->integer('min_order_value')->default(0); 
        $table->timestamp('expires_at')->nullable(); 
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
