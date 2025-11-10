<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('order_id'); // khóa chính
            $table->unsignedBigInteger('user_id'); // liên kết users
            $table->unsignedBigInteger('payment_id'); // khóa ngoại tới bảng payments
            $table->string('order_name');
            $table->string('order_address');
            $table->string('order_phone', 20);
            $table->string('order_email')->nullable();
            $table->double('total_price')->default(0);
            $table->enum('status', ['Chờ thanh toán', 'Đang xử lý', 'Đã xác nhận', 'Đang giao', 'Hoàn thành', 'Đã hủy'])->default('Đang xử lý');
            $table->string('order_note');
            $table->timestamps();

            // Khóa ngoại
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('payment_id')->references('payment_id')->on('payments')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};


