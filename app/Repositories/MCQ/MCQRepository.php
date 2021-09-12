<?php

namespace App\Repositories\MCQ;

use App\Models\MCQ;
use Illuminate\Support\Arr;

class MCQRepository implements MCQInterface
{
    /**
     * @var MCQ
     */
    private $model;

    public function __construct(MCQ $model)
    {
        $this->model = $model;
    }

    public function getAll($request)
    {
        return $this->model->orderBy('id', 'desc')->paginate($request->perPage);
    }

    public function getById($id)
    {
        return $this->model->find($id);
    }

    public function create(array $attr)
    {
        return $this->model->create([
            'author_id' => auth()->user()->id,
            'question' => $attr['question'],
            'option_1' => $attr['option_1'],
            'option_2' => $attr['option_2'],
            'option_3' => Arr::get($attr, 'option_3', null),
            'option_4' => Arr::get($attr, 'option_4', null),
            'option_5' => Arr::get($attr, 'option_5', null),
            'correct_answer_no' => $attr['correct_answer_no'],
        ]);
    }

    public function update($id, array $attr)
    {
        $data = $this->model->findOrFail($id);
        $data->update([
            'question' => $attr['question'],
            'option_1' => $attr['option_1'],
            'option_2' => $attr['option_2'],
            'option_3' => Arr::get($attr, 'option_3', null),
            'option_4' => Arr::get($attr, 'option_4', null),
            'option_5' => Arr::get($attr, 'option_5', null),
            'correct_answer_no' => $attr['correct_answer_no'],
        ]);

        return $data;
    }

    public function delete($id)
    {
        return $this->model->findOrFail($id)->delete();
    }
}
