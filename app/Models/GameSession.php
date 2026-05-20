<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GameSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'team_id',
        'city_id',
        'start_time',
        'end_time',
        'status',
        'total_score',
        'total_coins',
        'total_hearts',
        'current_enigma_id',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function currentEnigma()
    {
        return $this->belongsTo(Enigma::class, 'current_enigma_id');
    }

    public function enigmaResponses()
    {
        return $this->hasMany(EnigmaResponse::class);
    }
}
