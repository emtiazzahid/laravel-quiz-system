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

    public function saveTest(Request $request)
    {
        //TODO:: AUTOSAVE FEATURE
    }

    public function start(Request $request)
    {
        //PUT RECORD FOR PARTICIPATE
    }

    public function complete($id, Request $request)
    {
        $request->validate([
            'mcqs' => 'required|array|min:1'
        ]);


        return $this->quizTest->processTest($id, $request);
    }
}
