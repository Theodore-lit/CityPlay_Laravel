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
            if (!Schema::hasColumn('game_sessions', 'final_score')) {
                $table->integer('final_score')->default(0);
            }
            if (!Schema::hasColumn('game_sessions', 'total_time')) {
                $table->integer('total_time')->default(0); // en secondes
            }
            if (!Schema::hasColumn('game_sessions', 'items_found')) {
                $table->integer('items_found')->default(0);
            }
            if (!Schema::hasColumn('game_sessions', 'date_completion')) {
                $table->timestamp('date_completion')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('game_sessions', function (Blueprint $table) {
            $table->dropColumn(['final_score', 'total_time', 'items_found', 'date_completion']);
        });
    }
};
