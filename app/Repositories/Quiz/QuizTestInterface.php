<?php

namespace App\Repositories\Quiz;

interface QuizTestInterface
{
    public function getMCQList($id);

    public function getResult($id);

    public function getAllAttempts($request);
}
