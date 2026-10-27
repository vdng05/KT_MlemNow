<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->unique()->nullable(); // Dành cho khách chưa đăng nhập
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->timestamps(); // Cột updated_at sẽ được Command dùng để quét rác 24h
        });
    }

    public function down(): void {
        Schema::dropIfExists('carts');
    }
};