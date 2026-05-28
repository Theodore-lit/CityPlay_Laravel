<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // kamal
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('city_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->json('images')->nullable(); // Pour stocker plusieurs images
            $table->dateTime('event_date')->nullable();
            $table->string('location_name')->nullable();
            $table->integer('diamond_price')->default(0); //kamal: Coût du pass en diamants
            $table->boolean('has_vip_pass')->default(false); // kamal: pour savoir si l'évènement offre des pass ou non
            $table->string('reward_type')->nullable(); // kamal: type de recompense 'ticket', 'meal', 'discount'
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('city_events');
    }
};
