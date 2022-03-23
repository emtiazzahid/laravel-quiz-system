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
        if (!empty($request->title)) {
            $quizzes = Quiz::search($request->title);
            return $quizzes->paginate($request->perPage);
        }

        return Quiz::paginate($request->perPage);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getQuizById($id)
    {
        return Quiz::find($id);
    }

    // use this method if scout search is disabled or not using
    private function regularSearch($query)
    {
        $quizzes =  Quiz::orderBy('id', 'desc');

        $quizzes->where('title', 'like', '%'.$query.'%')
            ->orWhereHas('author', function ($q) use ($query) {
                $q->where('name', 'like', '%'.$query.'%');
            });

        return $quizzes;
    }
}
