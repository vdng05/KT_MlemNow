<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained()->cascadeOnDelete();
            $table->foreignId('food_id')->constrained()->cascadeOnDelete();
            $table->string('size_name')->nullable();
            $table->string('toppings')->nullable(); // Lưu chuỗi topping (VD: Phô mai • Xúc xích)
            $table->integer('quantity');
            $table->integer('price'); // Giá lúc thêm vào giỏ (đã gồm size/topping)
            $table->string('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('cart_items');
    }
};