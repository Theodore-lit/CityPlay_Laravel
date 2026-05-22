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
        Schema::create('competitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_event_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            
            // Type de compétition: 'ranking' (Top 1/3) ou 'fixed' (atteindre un seuil)
            $table->enum('type', ['ranking', 'fixed'])->default('fixed');
            
            // Type d'objectif: 'xp', 'hearts', 'diamonds'
            $table->enum('objective_type', ['xp', 'hearts', 'diamonds'])->default('xp');
            
            // Pour le type 'fixed', le montant à atteindre
            $table->integer('goal_amount')->nullable();
            
            // Pour le type 'ranking', le nombre de gagnants (1 ou 3)
            $table->integer('ranking_limit')->nullable();

            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->string('reward_description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Table pivot pour suivre la participation et le score actuel
        Schema::create('competition_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('current_amount')->default(0);
            $table->boolean('is_winner')->default(false);
            $table->boolean('reward_claimed')->default(false);
            $table->timestamps();

            $table->unique(['competition_id', 'user_id']);
        });

        // Table pour les lots gagnés (prizes) à ouvrir dans rewards.vue
        Schema::create('user_prizes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('competition_id')->nullable()->constrained()->onDelete('set null');
            $table->string('title');
            $table->string('description')->nullable();
            $table->string('reward_type'); // ex: 'diamonds', 'special_pass'
            $table->integer('reward_value')->nullable();
            $table->boolean('is_opened')->default(false);
            $table->string('qr_code_data')->nullable(); // Données pour le QR Code
            $table->timestamp('opened_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_prizes');
        Schema::dropIfExists('competition_user');
        Schema::dropIfExists('competitions');
    }
};
