<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Enigma extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_id',
        'title',
        'content',
        'difficulty',
        'indices',
        'answer',
        'reward_coins',
        'reward_hearts',
        'penalty_seconds',
        'time_limit_seconds',
        'type',
        'is_site_specific',
        'sequence_order',
        'image_path',
    ];

    protected $casts = [
        'indices' => 'array',
        'is_site_specific' => 'boolean',
    ];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function questions()
    {
        return $this->hasMany(EnigmaQuestion::class);
    }

    public function responses()
    {
        return $this->hasMany(EnigmaResponse::class);
    }
}
