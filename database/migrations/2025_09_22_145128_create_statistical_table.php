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
        Schema::create('statistical', function (Blueprint $table) {
            $table->id();
            $table->date('order_date')->unique();          // ngày thống kê (YYYY-MM-DD)
            $table->unsignedInteger('order_count')->default(0); // số đơn hoàn tất trong ngày
            $table->unsignedBigInteger('sales')->default(0);    // doanh thu (VND)
            $table->unsignedBigInteger('profit')->default(0);   // lợi nhuận (VND)
            $table->unsignedInteger('quantity')->default(0);    // tổng SLSP bán ra
            $table->timestamps();

            $table->index('order_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('statistical');
    }
};
