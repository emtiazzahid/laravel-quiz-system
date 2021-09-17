<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'author_id',
        'description',
        'time_limit',
        'high_score',
        'high_scorer_id',
        'digest_email'
    ];

    public function mcqs()
    {
        return $this->belongsToMany(MCQ::class, 'quiz_m_c_q_s','quiz_id', 'mcq_id');
    }

    public function author()
    {
        return $this->belongsTo(User::class,'author_id');
    }

    public function high_scorer_user()
    {
        return $this->belongsTo(User::class,'high_scorer_id');
    }

    public function quiz_attempts()
    {
        return $this->hasMany(QuizAttempt::class);
    }
}
