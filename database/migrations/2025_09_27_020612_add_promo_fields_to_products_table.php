<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('products', function (Blueprint $table) {
            // giá sau giảm (để null nghĩa là không khuyến mãi)
            $table->decimal('promo_price', 15, 0)->nullable()->after('product_price');
            // thời gian áp dụng
            $table->timestamp('promo_start')->nullable()->after('promo_price');
            $table->timestamp('promo_end')->nullable()->after('promo_start');
        });
    }
    public function down(): void {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['promo_price','promo_start','promo_end']);
        });
    }
};
