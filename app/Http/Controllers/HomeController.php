<?php

namespace App\Http\Controllers;

use App\Http\Resources\QuizResource;
use App\Repositories\Homepage\HomeInterface;
use Illuminate\Http\Request;

class HomeController extends BaseController
{
    private $home;

    public function __construct(HomeInterface $home){
        $this->home = $home;
    }

    /**
     * @param Request $request
     */
    public function quizzes(Request $request)
    {
        return QuizResource::collection(
            $this->home->getQuizzes($request)
        );
    }

    /**
     * @param $id
     * @return QuizResource
     */
    public function quiz($id): QuizResource
    {
        return new QuizResource(
            $this->home->getQuizById($id)
        );
    }
}
