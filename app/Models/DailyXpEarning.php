<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyXpEarning extends Model
{
    protected $fillable = ['user_id', 'date', 'xp_earned'];
    protected $casts = ['date' => 'date'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
