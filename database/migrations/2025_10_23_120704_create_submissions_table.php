<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('submissions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('dealer_id')->constrained()->cascadeOnDelete();
            $t->uuid('submission_id')->unique();
            $t->string('full_name');
            $t->string('email')->nullable();
            $t->string('phone')->nullable();
            $t->integer('guest_count')->default(1);
            $t->boolean('wants_appointment')->default(false);
            $t->text('notes')->nullable();
            $t->json('meta')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};
