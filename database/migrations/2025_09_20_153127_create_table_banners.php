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
        Schema::create('banners', function (Blueprint $table) {
            $table->id(); // id (auto-increment, unsigned)
            $table->string('title')->nullable();
            $table->string('image_path'); // NOT NULL
            $table->string('link_url', 500)->nullable();
            $table->string('position', 50)->default('home_top'); // vị trí đặt banner
            $table->integer('sort_order')->default(0); // sắp xếp tăng dần
            $table->tinyInteger('status')->default(1); // 1=hiện, 0=ẩn
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('position', 'idx_banners_position');
            $table->index('status', 'idx_banners_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
