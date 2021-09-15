<?php

namespace App\Http\Resources;

use App\Enums\QuizAttemptStatus;
use Illuminate\Http\Resources\Json\JsonResource;

class QuizAttemptsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'total_mcq' => $this->total_mcq,
            'total_answered_mcq' => $this->total_answered_mcq,
            'total_correct_answer' => $this->total_correct_answer,
            'high_score' => $this->high_score,
            'status' => $this->status == QuizAttemptStatus::$RUNNING ? 'Running' : 'Completed',
            'score' => $this->score,
            'created_at' => $this->created_at,
            'quiz' => new QuizResource($this->quiz),
        ];
    }
}
