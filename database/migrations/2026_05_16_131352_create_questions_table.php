<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $column) {
            $column->id();
            $column->foreignId('quiz_id')->constrained()->onDelete('cascade');
            $column->text('question_text');
            $column->json('options'); // Store as ['A' => '...', 'B' => '...']
            $column->string('correct_option'); // 'A', 'B', 'C', or 'D'
            $column->text('explanation')->nullable();
            $column->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
