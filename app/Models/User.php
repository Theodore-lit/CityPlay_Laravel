<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'otp_code', 'otp_expires_at', 'is_verified', 'password', 'role', 'secret_code', 'coins', 'hearts', 'xp', 'level', 'avatar', 'is_active', 'last_activity_at', 'deactivate_on_logout', 'latitude', 'longitude'])]
#[Hidden(['password', 'remember_token', 'otp_code'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Définit les types de données pour les attributs du modèle.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'otp_expires_at' => 'datetime',
            'last_activity_at' => 'datetime',
            'is_verified' => 'boolean',
            'is_active' => 'boolean',
            'deactivate_on_logout' => 'boolean',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'expired_at' => 'datetime',
            'last_active_at' => 'datetime',
        ];
    }

    /**
     * Villes créées par cet utilisateur (si Mairie ou Admin).
     */
    public function createdCities()
    {
        return $this->hasMany(City::class, 'creator_id');
    }
    public function mairie()
    {
        return $this->belongsTo(City::class, 'mairie_id');
    }

    /**
     * Équipes dont l'utilisateur est membre.
     */
    public function teams()
    {
        return $this->belongsToMany(Team::class)->withPivot('role')->withTimestamps();
    }

    /**
     * Sessions de jeu actives ou passées de l'utilisateur.
     */
    public function gameSessions()
    {
        return $this->hasMany(GameSession::class);
    }

    /**
     * Progression détaillée sur chaque lieu.
     */
    public function locationProgress()
    {
        return $this->hasMany(UserLocationProgress::class);
    }

    /**
     * Lieux découverts par l'utilisateur via la relation pivot.
     */
    public function discoveredLocations()
    {
        return $this->belongsToMany(Location::class, 'user_location_progress')
            ->withPivot('stars', 'is_discovered', 'discovered_at')
            ->withTimestamps();
    }

    /**
     * Résultats des quiz effectués par l'utilisateur.
     */
    public function quizResults()
    {
        return $this->hasMany(QuizResult::class);
    }
}
