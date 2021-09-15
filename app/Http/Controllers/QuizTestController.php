<?php

namespace App\Http\Controllers;

use App\Http\Requests\QuizRequest;
use App\Http\Resources\PublicQuizResource;
use App\Http\Resources\QuizResource;
use App\Repositories\Quiz\QuizInterface;
use App\Repositories\Quiz\QuizTestInterface;
use Illuminate\Http\Request;

class QuizTestController extends BaseController
{
    private $quizTest;

    public function __construct(QuizTestInterface $quizTest){
        $this->quizTest = $quizTest;
    }

    /**
     * @param $id
     * @return PublicQuizResource
     */
    public function mcqList($id)
    {
        return new PublicQuizResource( $this->quizTest->getMCQList($id) );
    }

    /**
     * @param $id
     * @param Request $request
     * @return array
     */
    public function start($id, Request $request)
    {
        $request->validate([
            'attempt_id' => 'nullable' // Will be null when a test is being initialized
        ]);

        return $this->quizTest->startQuiz($id, $request->attempt_id);
    }
}
