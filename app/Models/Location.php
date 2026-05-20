<?php

namespace App\Models;

use App\Support\StorageUrl;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'city_id',
        'name',
        'description',
        'category',
        'popularity',
        'latitude',
        'longitude',
        'radius_meters',
        'average_time_minutes',
        'images',
        'is_secret',
        'status',
    ];

    protected $casts = [
        'images' => 'array',
        'is_secret' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    protected $appends = [
        'cover_image',
        'image_urls',
    ];

    protected function coverImage(): Attribute
    {
        return Attribute::get(fn () => StorageUrl::url(($this->images ?? [])[0] ?? null));
    }

    protected function imageUrls(): Attribute
    {
        return Attribute::get(fn () => StorageUrl::urls($this->images ?? []));
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function enigmas()
    {
        return $this->hasMany(Enigma::class);
    }

    public function quizzes()
    {
        return $this->hasMany(Quiz::class);
    }

    public function userProgress()
    {
        return $this->hasMany(UserLocationProgress::class);
    }
}
