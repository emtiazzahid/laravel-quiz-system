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
        if (auth()->check()) {
            $this->model = $model->where('author_id', auth()->user()->id);
        } else {
            $this->model = $model;
        }
    }

    public function getAll($request)
    {
        return $this->model->orderBy('id', 'desc')
            ->paginate($request->perPage);
    }

    public function getById($id)
    {
        return $this->model->find($id);
    }

    public function create(array $attr)
    {
        return $this->model->create(array_merge([
            'author_id' => auth()->user()->id],
            $this->mcqArray($attr)
        ));
    }

    public function update($id, array $attr)
    {
        $data = $this->model->findOrFail($id);
        $data->update($this->mcqArray($attr));

        return $data;
    }

    public function delete($id)
    {
        return $this->model->findOrFail($id)->delete();
    }

    private function mcqArray($attr)
    {
        return [
            'question' => $attr['question'],
            'option_1' => $attr['option_1'],
            'option_2' => $attr['option_2'],
            'option_3' => Arr::get($attr, 'option_3', null),
            'option_4' => Arr::get($attr, 'option_4', null),
            'option_5' => Arr::get($attr, 'option_5', null),
            'correct_answer_no' => $attr['correct_answer_no'],
        ];
    }
}
