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
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->integer('radius_meters')->default(5000);
            $table->boolean('is_active')->default(true);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->json('opening_hours')->nullable();
            $table->json('forbidden_zones')->nullable();
            $table->foreignId('creator_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('mairie_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cities');
    }
};
