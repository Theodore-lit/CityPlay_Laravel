<?php

namespace App\Models;

use App\Support\StorageUrl;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Modèle représentant une Énigme associée à un lieu.
 * Peut être une énigme d'indice (pour trouver le lieu) ou spécifique au site (une fois sur place).
 */
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

    /**
     * Casts des attributs.
     */
    protected $casts = [
        'indices' => 'array',
        'is_site_specific' => 'boolean',
    ];

    protected $appends = [
        'image_url',
    ];

    /**
     * Accesseur pour l'image d'illustration de l'énigme.
     */
    protected function imageUrl(): Attribute
    {
        return Attribute::get(fn () => StorageUrl::url($this->image_path));
    }

    /**
     * Relation : Une énigme appartient à un Lieu.
     */
    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Relation : Une énigme peut contenir plusieurs questions (Mode Quiz/Questionnaire).
     */
    public function questions()
    {
        return $this->hasMany(EnigmaQuestion::class);
    }

    /**
     * Relation : Réponses des utilisateurs à cette énigme.
     */
    public function responses()
    {
        return $this->hasMany(EnigmaResponse::class);
    }
}
