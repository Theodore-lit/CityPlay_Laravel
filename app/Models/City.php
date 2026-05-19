<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Modèle représentant une Ville dans le jeu.
 * Géré principalement par les mairies ou administrateurs.
 */
class City extends Model
{
    use HasFactory;

    /**
     * Attributs assignables en masse.
     */
    protected $fillable = [
        'name',             // Nom de la ville (ex: Cotonou Vibrante)
        'description',      // Description textuelle pour les joueurs
        'image_path',       // Chemin vers l'image de couverture
        'latitude',
        'longitude',
        'radius_meters',
        'is_active',        // Statut d'activation de la ville
        'start_date',       // Date de début de l'événement
        'end_date',         // Date de fin de l'événement
        'opening_hours',    // Horaires de jeu autorisés (JSON)
        'forbidden_zones',  // Zones hors-jeu (JSON)
        'creator_id',       // ID de l'utilisateur (mairie/admin) créateur
    ];

    /**
     * Casts des attributs pour faciliter la manipulation.
     */
    protected $casts = [
        'opening_hours' => 'array',
        'forbidden_zones' => 'array',
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Relation : Une ville appartient à un créateur (User).
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /**
     * Relation : Une ville contient plusieurs lieux (Locations).
     */
    public function locations()
    {
        return $this->hasMany(Location::class);
    }

    /**
     * Relation : Une ville contient plusieurs quiz.
     */
    public function quizzes()
    {
        return $this->hasMany(Quiz::class);
    }

    /**
     * Relation : Une ville contient plusieurs événements.
     */
    public function events()
    {
        return $this->hasMany(CityEvent::class);
    }

    /**
     * Relation : Une ville peut avoir plusieurs sessions de jeu actives ou passées.
     */
    public function gameSessions()
    {
        return $this->hasMany(GameSession::class);
    }
}
