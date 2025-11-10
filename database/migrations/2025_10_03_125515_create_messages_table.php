<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('messages', function (Blueprint $t) {
            $t->id();
            $t->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $t->enum('direction', ['in','out']); // in: từ khách -> hệ thống; out: từ nhân viên -> khách
            $t->unsignedBigInteger('sender_id')->nullable(); // id nhân viên (out) hoặc null nếu khách vãng lai
            $t->string('sender_name')->nullable();           // hiển thị
            $t->text('body');
            $t->timestamp('read_at')->nullable();
            $t->timestamps();
            $t->index(['conversation_id','created_at']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('messages');
    }
};
