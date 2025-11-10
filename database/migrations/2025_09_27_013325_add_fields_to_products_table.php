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
            // nồng độ cồn, ví dụ 14.5 (%)
            $table->decimal('alcohol_percent', 4, 1)
                  ->nullable()
                  ->after('product_description');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('alcohol_percent');
        });
    }
};
