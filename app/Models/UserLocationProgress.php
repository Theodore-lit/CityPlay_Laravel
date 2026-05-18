<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserLocationProgress extends Model
{
    protected $table = 'user_location_progress';

    protected $fillable = [
        'user_id',
        'location_id',
        'stars',
        'is_discovered',
        'discovered_at',
    ];

    protected $casts = [
        'is_discovered' => 'boolean',
        'discovered_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
