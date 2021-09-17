<?php

namespace App\Http\Controllers;

use App\Http\Resources\QuizAttemptsResource;
use App\Repositories\Quiz\QuizTestInterface;
use Illuminate\Http\Request;

class QuizAttemptController extends BaseController
{
    private $quizTest;

    /**
     * @param QuizTestInterface $quizTest
     */
    public function __construct(QuizTestInterface $quizTest){
        $this->quizTest = $quizTest;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        return QuizAttemptsResource::collection(
            $this->quizTest->getAllAttempts($request)
        );
    }

    /**
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function complete($id, Request $request)
    {
        $request->validate([
            'mcqs' => 'required|array|min:1'
        ]);

        $this->quizTest->processTest($id, $request->mcqs);

        return $this->sendResponse('','Quiz attempt processed successfully');
    }

    /**
     * @param $id
     * @return mixed
     */
    public function result($id)
    {
        return $this->quizTest->getResult($id);
    }
}
