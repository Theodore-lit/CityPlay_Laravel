<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Competition extends Model
{
    use HasFactory;

    protected $fillable = [
        'city_event_id',
        'title',
        'description',
        'type', // 'ranking', 'fixed'
        'objective_type', // 'xp', 'hearts', 'diamonds'
        'goal_amount',
        'ranking_limit',
        'start_date',
        'end_date',
        'reward_description',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
        'goal_amount' => 'integer',
        'ranking_limit' => 'integer',
    ];

    /**
     * Une compétition appartient à un événement de ville.
     */
    public function cityEvent(): BelongsTo
    {
        return $this->belongsTo(CityEvent::class);
    }

    /**
     * Les joueurs participant à cette compétition.
     */
    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'competition_user')
            ->withPivot(['current_amount', 'is_winner', 'reward_claimed'])
            ->withTimestamps();
    }

    /**
     * Calcule la progression d'un utilisateur vers l'objectif.
     */
    public function getProgress(User $user)
    {
        if ($this->type === 'ranking') {
            return null; // Pas de jauge fixe pour le classement
        }

        $participant = $this->participants()->where('user_id', $user->id)->first();
        if (!$participant || !$this->goal_amount) {
            return 0;
        }

        return min(100, round(($participant->pivot->current_amount / $this->goal_amount) * 100));
    }
}
