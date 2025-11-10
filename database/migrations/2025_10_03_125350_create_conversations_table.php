<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('conversations', function (Blueprint $t) {
            $t->id();
            $t->string('session_id')->nullable()->index();   // ghim khách theo session
            $t->unsignedBigInteger('user_id')->nullable();   // nếu khách đã đăng nhập
            $t->string('customer_name')->nullable();
            $t->string('customer_contact')->nullable();      // email/phone tùy bạn thu
            $t->enum('status', ['open','closed'])->default('open');
            $t->timestamp('last_message_at')->nullable()->index();
            $t->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('conversations');
    }
};
