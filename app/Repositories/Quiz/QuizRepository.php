<?php

namespace App\Repositories\Quiz;

use App\Models\Quiz;

class QuizRepository implements QuizInterface
{
    /**
     * @var Quiz
     */
    private $model;

    public function __construct(Quiz $model)
    {
        if (auth()->check()) {
            $this->model = $model->where('author_id', auth()->user()->id);
        } else {
            $this->model = $model;
        }
    }

    public function getAll($request)
    {
        return $this->model->with(['author', 'high_scorer_user'])->orderBy('id', 'desc')
            ->paginate($request->perPage);
    }

    public function getById($id)
    {
        return $this->model->with(['author', 'high_scorer_user'])->find($id);
    }

    public function create(array $attr)
    {
        return $this->model->create([
            'title' => $attr['title'],
            'author_id' => auth()->user()->id,
            'description' => $attr['description'],
            'time_limit' => $attr['time_limit']
        ]);
    }

    public function update($id, array $attr)
    {
        $data = $this->model->findOrFail($id);
        $data->update([
            'title' => $attr['title'],
            'description' => $attr['description'],
            'time_limit' => $attr['time_limit']
        ]);

        return $data;
    }

    public function delete($id)
    {
        return $this->model->findOrFail($id)->delete();
    }

    public function getMCQ($id)
    {
        return $this->model->with('mcqs')
            ->findOrFail($id);
    }

    public function updateMCQ($id, $mcq_ids)
    {
        return $this->model->findOrFail($id)
            ->mcqs()
            ->sync($mcq_ids);
    }
}
