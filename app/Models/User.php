<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'coins', 'hearts', 'xp', 'level', 'avatar', 'is_active', 'last_activity_at', 'deactivate_on_logout'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function createdCities()
    {
        return $this->hasMany(City::class, 'creator_id');
    }

    public function teams()
    {
        return $this->belongsToMany(Team::class)->withPivot('role')->withTimestamps();
    }

    public function gameSessions()
    {
        return $this->hasMany(GameSession::class);
    }

    public function locationProgress()
    {
        return $this->hasMany(UserLocationProgress::class);
    }

    public function discoveredLocations()
    {
        return $this->belongsToMany(Location::class, 'user_location_progress')
            ->withPivot('stars', 'is_discovered', 'discovered_at')
            ->withTimestamps();
    }

    public function quizResults()
    {
        return $this->hasMany(QuizResult::class);
    }
}
