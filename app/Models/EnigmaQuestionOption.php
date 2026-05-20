<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EnigmaQuestionOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'enigma_question_id',
        'option_text',
        'is_correct',
    ];

    public function question()
    {
        return $this->belongsTo(EnigmaQuestion::class, 'enigma_question_id');
    }
}
