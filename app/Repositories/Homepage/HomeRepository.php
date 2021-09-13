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
        return Quiz::orderBy('id', 'desc')
            ->paginate($request->perPage);
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
