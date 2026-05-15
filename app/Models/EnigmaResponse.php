<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EnigmaResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_session_id',
        'enigma_id',
        'status',
        'attempts',
        'time_spent_seconds',
        'indices_used_count',
    ];

    public function gameSession()
    {
        return $this->belongsTo(GameSession::class);
    }

    public function enigma()
    {
        return $this->belongsTo(Enigma::class);
    }
}
