<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quizzes', function (Blueprint $column) {
            $column->id();
            $column->foreignId('city_id')->nullable()->constrained()->onDelete('cascade');
            $column->foreignId('location_id')->nullable()->constrained()->onDelete('cascade');
            $column->string('title');
            $column->text('description')->nullable();
            $column->string('category');
            $column->integer('xp_reward')->default(100);
            $column->integer('time_limit')->default(60); // seconds per quiz or question
            $column->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};
