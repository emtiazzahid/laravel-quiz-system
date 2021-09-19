<?php

namespace Tests\Unit;

use App\Models\MCQ;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use App\Repositories\Quiz\QuizRepository;
use App\Repositories\Quiz\QuizTestRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\HttpException;
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
        $this->assertEquals($attempt->score, 0); // Match 0 because no mcq answered has been given to process
    }

    public function test_can_sync_own_quiz_mcqs()
    {
        $this->actingAsUser();
        $this->withoutMiddleware();

        $mcqs = MCQ::factory()->count(10)->create([
            'author_id' => \auth()->user()->id
        ])->pluck('id')->toArray();

        $quiz = Quiz::factory()
            ->has(MCQ::factory()->count(2), 'mcqs')
            ->create([
                'author_id' => \auth()->user()->id
            ]);

        $detaching_ids = $quiz->mcqs->pluck('id')->toArray();

        $result = (new QuizRepository(new Quiz()))->updateMCQ($quiz->id, $mcqs);

        $this->assertEquals($result, [
            'attached' => $mcqs,
            'detached' => $detaching_ids,
            'updated' => [],
        ]);
    }

    public function test_cant_update_others_mcq_ids_of_their_quiz()
    {
        $this->actingAsUser();
        $this->withoutMiddleware();

        $unauthorized_user = \auth()->user();

        $legal_user = User::factory()->create();

        // Legal user data
        $mcqs = MCQ::factory()->count(10)->create([
            'author_id' => $legal_user
        ])->pluck('id')->toArray();

        $quiz = Quiz::factory()
            ->has(MCQ::factory()->count(1), 'mcqs')
            ->create([
                'author_id' => $unauthorized_user->id
            ]);

        try {
            (new QuizRepository(new Quiz()))->updateMCQ($quiz->id, $mcqs);
        } catch (\Throwable $e) {}

        $this->assertEquals(
            new HttpException(401, 'Unauthorized MCQ sync'),
            $e
        );
    }
}
