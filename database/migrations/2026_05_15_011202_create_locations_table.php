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
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_id')->constrained('cities')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category'); // historique, restaurant, monument, culture, mystere, nature
            $table->integer('popularity')->default(0);
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->integer('radius_meters')->default(50);
            $table->integer('average_time_minutes')->nullable();
            $table->json('images')->nullable();
            $table->boolean('is_secret')->default(false);
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
