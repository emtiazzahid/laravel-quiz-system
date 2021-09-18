<?php

namespace App\Repositories\Quiz;

interface QuizTestInterface
{
    public function getAllAttempts($request);

    public function getMCQList($id);

    public function processTest($attempt_id, $answered_mcqs);

    public function getResult($id);

    public function startQuiz($id, $attempt_id);

    public function digestReport($id);
}
