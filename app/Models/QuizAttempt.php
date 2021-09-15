<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizAttempt extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = ['quiz_id', 'user_id', 'status', 'score', 'created_at'];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }
}
