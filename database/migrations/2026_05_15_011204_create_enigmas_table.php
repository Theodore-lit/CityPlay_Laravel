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
        Schema::create('enigmas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->constrained()->onDelete('cascade');
            $table->string('title')->nullable();
            $table->text('content');
            $table->string('difficulty'); // easy, medium, hard
            $table->json('indices')->nullable();
            $table->string('answer');
            $table->integer('reward_coins')->default(0);
            $table->integer('reward_hearts')->default(0);
            $table->integer('penalty_seconds')->default(0);
            $table->integer('time_limit_seconds')->nullable();
            $table->string('type')->default('devinette'); // devinette, aventure
            $table->boolean('is_site_specific')->default(false); // true if riddle is only solvable on site
            $table->integer('sequence_order')->default(0);
            $table->string('image_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enigmas');
    }
};
