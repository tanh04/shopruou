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
    { Schema::create('visitors', function (Blueprint $t) {
            $t->id();
            $t->string('session_id', 100);          // log 1 bản ghi / session / ngày
            $t->string('ip', 45);
            $t->string('user_agent', 255)->nullable();
            $t->date('visit_date');                 // để unique theo ngày
            $t->timestamp('visited_at')->index();   // lần ghé cuối (phục vụ "đang online")
            $t->timestamps();

            $t->unique(['session_id','visit_date']);
            $t->index('visit_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitors');
    }
};
