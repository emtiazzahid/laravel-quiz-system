<?php

namespace Tests\Unit;

use App\Models\MCQ;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Repositories\Quiz\QuizTestRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuizTest extends TestCase
{
    use RefreshDatabase;

    public function test_quiz_attempt_can_be_completed()
    {
        $this->actingAsUser();
        $this->withoutMiddleware();

        $quiz = Quiz::factory()->count(1)->create()->each(function($c) {
            $c->mcqs()->saveMany(
                MCQ::factory()->count(10)->create()
            );
        });

        $attempt = QuizAttempt::create([
            'quiz_id' => $quiz->first()->id,
            'user_id' => \auth()->user()->id,
        ]);

        $result = (new QuizTestRepository(new Quiz()))->processTest($attempt->id);

        $this->assertEquals($result, true);

        //Check is score updated
        $attempt = QuizAttempt::find($attempt->id);
        $this->assertEquals($attempt->score, 0); // Match 0 becouse no mcq answered has been given to process
    }
}
