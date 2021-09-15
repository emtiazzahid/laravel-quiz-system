<?php

namespace App\Http\Controllers;

use App\Http\Resources\QuizAttemptsResource;
use App\Repositories\Quiz\QuizTestInterface;
use Illuminate\Http\Request;

class QuizAttemptController extends Controller
{
    private $quizTest;

    public function __construct(QuizTestInterface $quizTest){
        $this->quizTest = $quizTest;
    }

    public function index(Request $request)
    {
        return QuizAttemptsResource::collection(
            $this->quizTest->getAllAttempts($request)
        );
    }

    public function complete($id, Request $request)
    {
        $request->validate([
            'mcqs' => 'required|array|min:1'
        ]);

        return $this->quizTest->processTest($id, $request->mcqs);
    }

    public function result($id)
    {
        return $this->quizTest->getResult($id);
    }
}
