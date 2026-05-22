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
        Schema::dropIfExists('enigma_responses');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('enigma_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('enigma_id')->constrained()->onDelete('cascade');
            $table->text('response_text')->nullable();
            $table->boolean('is_correct')->default(false);
            $table->integer('earned_coins')->default(0);
            $table->integer('earned_xp')->default(0);
            $table->timestamps();
        });
    }
};
