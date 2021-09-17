<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class QuizResource extends JsonResource
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
            'author' => optional($this->author)->name,
            'author_email' => optional($this->author)->email,
            'title' => $this->title,
            'description' => $this->description,
            'time_limit' => $this->time_limit,
            'high_score' => $this->high_score,
            'high_scorer_id' => $this->high_scorer_id,
            'high_scorer_name' => optional($this->high_scorer_user)->name,
            'digest_email' => $this->digest_email,
        ];
    }
}
