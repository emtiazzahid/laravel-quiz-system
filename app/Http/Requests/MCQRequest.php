<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MCQRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'question' => 'required',
            'option_1' => 'required',
            'option_2' => 'required',
            'correct_answer_no' => 'required|numeric',
        ];
    }
}
