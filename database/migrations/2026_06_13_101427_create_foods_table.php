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
        Schema::create('food', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->integer('base_price');
            $table->integer('sale_price')->nullable(); // Giá sau khuyến mãi
            $table->boolean('is_available')->default(true); // Xử lý ngoại lệ "Món ăn tạm hết hàng"
            $table->integer('prep_time')->default(15); // Thời gian chuẩn bị (phút)
            $table->integer('sold_count')->default(0); // Lượt đã bán
            $table->text('nutrition_info')->nullable();
            $table->text('ingredients')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('food');
    }
};
