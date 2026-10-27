<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->date('birth_date')->nullable();
            $table->string('avatar_path')->nullable();
            $table->boolean('is_staff')->default(false);
        });

        Schema::table('user_addresses', function (Blueprint $table) {
            $table->string('recipient_name')->nullable();
            $table->string('phone', 10)->nullable();
            $table->string('province')->nullable();
            $table->string('district')->nullable();
            $table->string('ward')->nullable();
            $table->string('detail')->nullable();
            $table->string('address_type')->nullable();
            $table->boolean('is_default')->default(false);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->enum('status', ['pending', 'confirmed', 'preparing', 'delivering', 'completed', 'cancelled'])->default('pending')->change();
            $table->string('cancellation_reason', 200)->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
        });

        Schema::create('order_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('from_status');
            $table->string('to_status');
            $table->timestamps();
        });

        Schema::create('order_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->string('comment', 500)->nullable();
            $table->json('images')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_reviews');
        Schema::dropIfExists('order_status_histories');

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['cancellation_reason', 'cancelled_at', 'reviewed_at']);
            $table->enum('status', ['pending', 'preparing', 'delivering', 'completed', 'cancelled'])->default('pending')->change();
        });

        Schema::table('user_addresses', function (Blueprint $table) {
            $table->dropColumn([
                'recipient_name', 'phone', 'province', 'district', 'ward',
                'detail', 'address_type', 'is_default',
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['birth_date', 'avatar_path', 'is_staff']);
        });
    }
};