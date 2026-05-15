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
        Schema::create('enigma_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_session_id')->constrained()->onDelete('cascade');
            $table->foreignId('enigma_id')->constrained()->onDelete('cascade');
            $table->string('status')->default('pending'); // pending, solved, failed
            $table->integer('attempts')->default(0);
            $table->integer('time_spent_seconds')->default(0);
            $table->integer('indices_used_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enigma_responses');
    }
};
