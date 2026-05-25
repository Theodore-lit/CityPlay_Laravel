<?php

namespace App\Models;

use App\Support\StorageUrl;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Modèle représentant un Lieu (POI) dans une ville.
 * Chaque lieu peut contenir des énigmes ou des quiz.
 */
class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'city_id',
        'name',
        'description',
        'history',
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

    /**
     * Casts des attributs pour faciliter la manipulation.
     */
    protected $casts = [
        'images' => 'array',
        'is_secret' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    protected $appends = [
        'cover_image',
        'image_urls',
        'image_url',
    ];

    /**
     * Alias pour cover_image pour assurer la compatibilité avec le composant AppImage
     */
    protected function imageUrl(): Attribute
    {
        return $this->coverImage();
    }

    /**
     * Accesseur pour l'image de couverture du lieu.
     */
    protected function coverImage(): Attribute
    {
        return Attribute::get(fn () => StorageUrl::url(($this->images ?? [])[0] ?? null));
    }

    /**
     * Accesseur pour transformer la liste des chemins d'images en URLs complètes.
     */
    protected function imageUrls(): Attribute
    {
        return Attribute::get(fn () => StorageUrl::urls($this->images ?? []));
    }

    /**
     * Relation : Un lieu appartient à une Ville.
     */
    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    /**
     * Relation : Un lieu possède plusieurs énigmes (Indice vs Site-specific).
     */
    public function enigmas()
    {
        return $this->hasMany(Enigma::class);
    }

    /**
     * Relation : Un lieu peut être lié à plusieurs Quiz.
     */
    public function quizzes()
    {
        return $this->hasMany(Quiz::class);
    }

    /**
     * Relation : Un lieu possède plusieurs images.
     */
    public function locationImages()
    {
        return $this->hasMany(LocationImage::class);
    }

    /**
     * Relation : Suivi de la progression des utilisateurs sur ce lieu.
     */
    public function userProgress()
    {
        return $this->hasMany(UserLocationProgress::class);
    }
}
