<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Lưu dạng text, cho phép nhiều giống nho, cách nhau bởi dấu phẩy
            $table->string('grape_variety', 255)
                  ->nullable()
                  ->after('alcohol_percent'); // đặt sau nồng độ cồn (đổi vị trí nếu cần)
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('grape_variety');
        });
    }
};
