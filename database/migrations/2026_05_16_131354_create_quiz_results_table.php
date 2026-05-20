<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_results', function (Blueprint $column) {
            $column->id();
            $column->foreignId('user_id')->constrained()->onDelete('cascade');
            $column->foreignId('quiz_id')->constrained()->onDelete('cascade');
            $column->integer('score');
            $column->integer('total_questions');
            $column->integer('xp_earned');
            $column->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_results');
    }
};
