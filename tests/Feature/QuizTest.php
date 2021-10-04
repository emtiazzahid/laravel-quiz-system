<?php

namespace Tests\Feature;

use App\Http\Requests\QuizRequest;
use App\Models\Quiz;
use App\Repositories\Quiz\QuizRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuizTest extends TestCase
{
    use RefreshDatabase;

    public function test_quiz_get_by_id()
    {
        $this->actingAsUser();
        $this->withoutMiddleware();

        $quiz = Quiz::factory()->create([
            'author_id' => auth()->user()->id
        ]);

        $result = (new QuizRepository(new Quiz()))->getById($quiz->id);
        $this->assertEquals($quiz->id, $result->id);
    }

    public function test_quiz_pagination()
    {
        $this->actingAsUser();
        $this->withoutMiddleware();

        $total_data = 40;
        $require_data = 1;

        $request = new QuizRequest();
        $request['perPage'] = $require_data;
        Quiz::factory()->count($total_data)->create([
            'author_id' => auth()->user()->id
        ]);

        $result = (new QuizRepository(new Quiz()))->getAll($request);

        $this->assertEquals($result->total(), $total_data);
        $this->assertEquals($result->count(), $require_data);
    }
}
