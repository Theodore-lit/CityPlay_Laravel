<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EventRedemption extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'city_event_id',
        'redemption_code',
        'redeemed_at',
    ];

    protected $casts = [
        'redeemed_at' => 'datetime',
    ];

    /**
     * Récupère l'utilisateur qui a réclamé la récompense.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Récupère l'événement lié à cette récompense.
     */
    public function cityEvent(): BelongsTo
    {
        return $this->belongsTo(CityEvent::class);
    }

    /**
     * Scope pour vérifier si un code a déjà été utilisé (si tu ajoutes une colonne status plus tard).
     */
    public function scopeIsRedeemed($query)
    {
        return $query->whereNotNull('redeemed_at');
    }
}
