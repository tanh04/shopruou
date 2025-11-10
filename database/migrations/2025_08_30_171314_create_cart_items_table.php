<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCartItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->Increments('id');

            // Cột khóa ngoại cart_id (liên kết với bảng 'carts')
            $table->unsignedInteger('cart_id');
            $table->foreign('cart_id')->references('cart_id')->on('carts')->onDelete('cascade');

            // Cột khóa ngoại product_id (liên kết với bảng 'products')
            $table->unsignedInteger('product_id');
            $table->foreign('product_id')->references('product_id')->on('products')->onDelete('cascade');

            $table->integer('quantity');  // Số lượng sản phẩm
            $table->double('price');  // Giá sản phẩm

            $table->timestamps();  // Các cột created_at và updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
}
