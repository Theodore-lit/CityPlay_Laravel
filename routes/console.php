<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\User;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    // Désactiver les utilisateurs inactifs depuis plus de 12 mois
    User::where('role', 'joueur')
        ->where('is_active', true)
        ->where(function($query) {
            $query->where('last_activity_at', '<', now()->subMonths(12))
                  ->orWhere(function($q) {
                      $q->whereNull('last_activity_at')
                        ->where('created_at', '<', now()->subMonths(12));
                  });
        })
        ->update(['is_active' => false]);
})->daily();
