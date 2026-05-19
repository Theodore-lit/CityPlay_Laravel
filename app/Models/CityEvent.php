<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CityEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'city_id',
        'title',
        'description',
        'images',
        'event_date',
        'location_name',
        'is_active',
    ];

    protected $casts = [
        'images' => 'array',
        'event_date' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function city()
    {
        return $this->belongsTo(City::class);
    }
}
