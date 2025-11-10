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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id('review_id');
            $table->unsignedInteger('product_id');
            $table->unsignedBigInteger('user_id');
            $table->tinyInteger('rating')->checkBetween(1, 5); // 1-5 sao
            $table->text('comment')->nullable();
            $table->tinyInteger('status')->default(0)->comment('0=ẩn,1=hiện');; // 1: hiển thị, 0: ẩn
            $table->timestamps();

            // Khóa ngoại
            $table->foreign('product_id')->references('product_id')->on('products')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
