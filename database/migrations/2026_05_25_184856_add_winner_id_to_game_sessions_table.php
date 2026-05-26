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
        Schema::table('game_sessions', function (Blueprint $table) {
            $table->foreignId('winner_id')->nullable()->constrained('users')->onDelete('set null');
            $table->uuid('lobby_session_id')->nullable()->index()->comment('Identifiant partagé pour les sessions multijoueurs');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('game_sessions', function (Blueprint $table) {
            try {
                $table->dropForeign('game_sessions_winner_id_foreign');
            } catch (\Exception $e) {
                // Foreign key might not exist
            }
            $table->dropColumn(['winner_id', 'lobby_session_id']);
        });
    }
};
