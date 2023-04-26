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
            $query = $request->title;

            $quizzes = $quizzes->where('title', 'like', '%'.$query.'%')
                ->orWhere('description', 'like', '%'.$query.'%')
                ->orWhereHas('author', function ($q) use ($query) {
                    $q->where('name', 'like', '%'.$query.'%');
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
