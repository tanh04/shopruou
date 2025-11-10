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
        Schema::table('products', function (Blueprint $table) {
            // VND: để kiểu DECIMAL(12,0). Nếu bạn cần lẻ, đổi thành (12,2)
            $table->decimal('cost_price', 12, 0)->nullable()
                  ->after('product_price'); // đặt cạnh giá bán cho dễ nhìn
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('cost_price');
        });
    }
};
