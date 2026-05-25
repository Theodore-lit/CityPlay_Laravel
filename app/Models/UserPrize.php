<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPrize extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'competition_id',
        'title',
        'description',
        'reward_type',
        'reward_value',
        'is_opened',
        'qr_code_data',
        'opened_at',
    ];

    protected $casts = [
        'is_opened' => 'boolean',
        'opened_at' => 'datetime',
        'reward_value' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }
}
