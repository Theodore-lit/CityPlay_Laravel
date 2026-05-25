<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EnigmaResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'enigma_id',
        'content',
        'is_correct',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
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
