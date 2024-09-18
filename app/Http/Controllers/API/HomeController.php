<?php

namespace App\Http\Controllers\API;

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
     * @param int $id
     * @return \Illuminate\Http\JsonResponse|QuizResource
     */
    public function quiz($id)
    {
        $quiz = $this->home->getQuizById($id);

        if (!$quiz) {
            return response()->json([
                'message' => 'Quiz not found'
            ], 404); // Return 404 Not Found if the quiz doesn't exist
        }

        return new QuizResource($quiz);
    }
}
