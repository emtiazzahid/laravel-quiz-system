<?php

namespace Tests\Unit;

use App\Models\Quiz;
use App\Repositories\Quiz\QuizRepository;
use Tests\TestCase;

class QuizTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_get_by_id()
    {
        $quiz = Quiz::factory()->create();
        $result = (new QuizRepository(new Quiz()))->getById($quiz->id);
        $this->assertEquals($quiz->id, $result->id);
    }
}
