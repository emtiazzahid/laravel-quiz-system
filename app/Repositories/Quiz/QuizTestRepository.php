<?php

namespace App\Repositories\Quiz;

use App\Models\MCQ;
use App\Models\Quiz;

class QuizTestRepository
{
    /**
     * @var Quiz
     */
    private $mcq;

    public function __construct(MCQ $mcq)
    {
        $this->mcq = $mcq;
    }

    public function getMCQList($request)
    {
        return;
    }
}
