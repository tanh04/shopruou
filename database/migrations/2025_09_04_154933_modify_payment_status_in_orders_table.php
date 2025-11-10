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
        Schema::table('payments', function (Blueprint $table) {
            // Thay đổi kiểu cột payment_status thành enum
            $table->enum('payment_status', ['Chưa thanh toán', 'Đang chờ xử lý', 'Đã thanh toán', 'Thanh toán thất bại'])
                  ->default('Đang chờ xử lý')
                  ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            //
        });
    }
};
