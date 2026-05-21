<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * Code de kamal
     */
    public function up(): void
    {
        Schema::create('event_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('city_event_id')->constrained()->onDelete('cascade');
            $table->string('redemption_code')->unique(); // Code QR ou matricule à présenter
            $table->timestamp('redeemed_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_redemptions');
    }
};
