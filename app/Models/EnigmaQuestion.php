<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EnigmaQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'enigma_id',
        'question_text',
    ];

    public function enigma()
    {
        return $this->belongsTo(Enigma::class);
    }

    public function options()
    {
        return $this->hasMany(EnigmaQuestionOption::class);
    }
}
