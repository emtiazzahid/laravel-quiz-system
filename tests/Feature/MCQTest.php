<?php

namespace Tests\Feature;

use App\Models\MCQ;
use App\Repositories\MCQ\MCQRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class MCQTest extends TestCase
{
    use RefreshDatabase, WithFaker;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_can_create_mcq()
    {
        $this->actingAsUser();
        $this->withoutMiddleware();

        $mcqObj = new MCQ();

        $author_id = auth()->user()->id;

        $current_answer = 2;

        $mcqData = [
            'author_id' => $author_id,
            'question' => $this->faker->sentence,
            'option_1' => $this->faker->sentence,
            'option_2' => $this->faker->sentence,
            'option_3' => $this->faker->sentence,
            'correct_answer_no' => $current_answer,
        ];

        (new MCQRepository($mcqObj))->create($mcqData);

        $this->assertDatabaseHas($mcqObj->getTable(), $mcqData);
    }
}
