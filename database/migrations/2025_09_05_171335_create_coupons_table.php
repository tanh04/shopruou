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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id('coupon_id');
            $table->string('coupon_code', 50)->unique(); // Mã giảm giá duy nhất
            $table->integer('coupon_quantity'); // Số lượng mã phát hành
            $table->decimal('discount_percent', 5, 2)->nullable(); // % giảm giá (vd: 10.50%)
            $table->decimal('discount_amount', 15, 2)->nullable(); // Giảm giá theo số tiền (vd: 50000)
            $table->decimal('min_order_value', 15, 2)->default(0); // Giá trị đơn hàng tối thiểu
            $table->date('start_date'); // Ngày bắt đầu
            $table->date('end_date');   // Ngày kết thúc
            $table->boolean('status')->default(true); // Trạng thái (1: còn hiệu lực, 0: hết hạn)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
