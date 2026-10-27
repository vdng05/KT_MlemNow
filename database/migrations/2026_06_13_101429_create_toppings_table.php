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
        Schema::create('toppings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('food_id')->constrained()->cascadeOnDelete();
            $table->string('name'); 
            $table->integer('extra_price')->default(0);
            $table->boolean('is_available')->default(true); // Xử lý ngoại lệ "Topping đã hết"
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('toppings');
    }
};
