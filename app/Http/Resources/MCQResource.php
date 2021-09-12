<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MCQResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $option = 'option_' . $this->correct_answer_no;
        return [
            'id' => $this->id,
            'question' => $this->question,
            'correct_answer' => $this->$option,
            'created_at' => $this->created_at
        ];
    }
}
