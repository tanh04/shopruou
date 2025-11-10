<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
 public function up(): void {
        Schema::table('reviews', function (Blueprint $table) {
            $table->boolean('verified_purchase')->default(false)->after('rating');

            // Mỗi user chỉ đánh giá 1 lần / sản phẩm (tuỳ bạn có muốn ràng buộc này hay không)
            $table->unique(['product_id','user_id'], 'reviews_product_user_unique');

            // Tối ưu lọc
            $table->index(['product_id','status'], 'reviews_product_status_idx');
        });
    }
    public function down(): void {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropIndex('reviews_product_status_idx');
            $table->dropUnique('reviews_product_user_unique');
            $table->dropColumn('verified_purchase');
        });
    }
};
