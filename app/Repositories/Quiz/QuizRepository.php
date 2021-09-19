<?php

namespace App\Repositories\Quiz;

use App\Models\MCQ;
use App\Models\Quiz;

class QuizRepository implements QuizInterface
{
    /**
     * @var Quiz
     */
    private $model;

    /**
     * @param Quiz $model
     */
    public function __construct(Quiz $model)
    {
        if (auth()->check()) {
            $this->model = $model->where('author_id', auth()->user()->id);
        } else {
            $this->model = $model;
        }
    }

    /**
     * @param $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAll($request)
    {
        return $this->model->with(['author', 'high_scorer_user'])
            ->orderBy('id', 'desc')
            ->paginate($request->perPage);
    }

    /**
     * @param $id
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|null
     */
    public function getById($id)
    {
        return $this->model->with(['author', 'high_scorer_user'])
            ->find($id);
    }

    /**
     * @param array $attr
     * @return mixed
     */
    public function create(array $attr)
    {
        return $this->model->create([
            'title' => $attr['title'],
            'author_id' => auth()->user()->id,
            'description' => $attr['description'],
            'time_limit' => $attr['time_limit']
        ]);
    }

    /**
     * @param $id
     * @param array $attr
     * @return mixed
     */
    public function update($id, array $attr)
    {
        $data = $this->model->findOrFail($id);
        $data->update($attr);

        return $data;
    }

    /**
     * @param $id
     * @param bool $status
     * @return mixed
     */
    public function updateDigestSettings($id, $status)
    {
        return $this->model->findOrFail($id)
            ->update(['digest_email' => boolval($status)]);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function delete($id)
    {
        return $this->model->findOrFail($id)
            ->delete();
    }

    /**
     * @param $id
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|null
     */
    public function getMCQ($id)
    {
        return $this->model->with('mcqs')
            ->findOrFail($id);
    }

    /**
     * @param $id
     * @param array $mcq_ids
     * @return mixed
     */
    public function updateMCQ($id, $mcq_ids)
    {
        $this->validateUserMCQIds($mcq_ids);

        return $this->model->findOrFail($id)
            ->mcqs()
            ->sync($mcq_ids);
    }

    /**
     * @param array $mcq_ids
     */
    private function validateUserMCQIds(array $mcq_ids)
    {
        $results = MCQ::where('author_id', auth()->user()->id)
            ->whereIn('id', $mcq_ids )->count();

        if($results !== count($mcq_ids)){
            abort(401, 'Unauthorized MCQ sync');
        }
    }
}
