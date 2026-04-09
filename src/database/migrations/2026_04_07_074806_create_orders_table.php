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
        // 建立與 users 表的關聯
        $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
        $table->string('order_number')->unique(); // 訂單編號
        $table->string('name');
        $table->string('phone');
        $table->string('address');
        $table->integer('total_amount');
        $table->string('payment_method'); // first: 信用卡, second: ATM
        $table->string('invoice_type');   // Option1, Option2, Option3
        $table->string('tax_id')->nullable(); // 統編
        $table->string('carrier')->nullable(); // 載具
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
