<?php

namespace App\Repositories\Homepage;

use App\Models\Quiz;

class HomeRepository implements HomeInterface
{
    /**
     * @param $request
     * @return mixed
     */
    public function getQuizzes($request)
    {
        $quizzes =  Quiz::orderBy('id', 'desc');

        if (!empty($request->title)) {
            $quizzes->where('title', 'like', '%'.$request->title.'%')
                ->orWhereHas('author', function ($q) use ($request) {
                    $q->where('name', 'like', '%'.$request->title.'%');
                });
        }

        return $quizzes->paginate($request->perPage);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getQuizById($id)
    {
        return Quiz::find($id);
    }
}
