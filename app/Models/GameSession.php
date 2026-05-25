<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Modèle représentant une session de jeu active (Solo ou Équipe).
 * Suit la progression, le score et l'état actuel de l'aventure.
 */
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
        'current_location_id',
        'discovery_sequence',
        'final_score',
        'total_time',
        'items_found',
        'date_completion',
        'winner_id',
        'lobby_session_id',
    ];

    /**
     * Casts des attributs temporels et complexes.
     */
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'date_completion' => 'datetime',
        'discovery_sequence' => 'array',
    ];

    /**
     * Relation : La session appartient à un Joueur (si Solo).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation : La session appartient à une Équipe (si Team Mode).
     */
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Relation : La session se déroule dans une Ville.
     */
    public function city()
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Relation : L'énigme actuellement en cours dans cette session.
     */
    public function currentEnigma()
    {
        return $this->belongsTo(Enigma::class, 'current_enigma_id');
    }

    /**
     * Relation : Le lieu (POI) actuellement visé ou en cours d'exploration.
     */
    public function currentLocation()
    {
        return $this->belongsTo(Location::class, 'current_location_id');
    }

    /**
     * Relation : L'utilisateur qui a gagné cette session.
     */
    public function winner()
    {
        return $this->belongsTo(User::class, 'winner_id');
    }

    /**
     * Relation : Tous les joueurs participants à cette session multijoueur.
     */
    public function players()
    {
        return $this->hasMany(GameSession::class, 'lobby_session_id', 'lobby_session_id')
                    ->where('id', '!=', $this->id);
    }

    /**
     * Relation : Les réponses aux énigmes.
     */
    public function enigmaResponses()
    {
        return $this->hasMany(EnigmaResponse::class);
    }
}
