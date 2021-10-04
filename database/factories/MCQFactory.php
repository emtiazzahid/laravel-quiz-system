<?php

namespace Database\Factories;

use App\Models\MCQ;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MCQFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MCQ::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'author_id' =>  User::get()->random()->id,
            'question' => $this->faker->sentence,
            'option_1' => $this->faker->sentence,
            'option_2' => $this->faker->sentence,
            'option_3' => $this->faker->sentence,
            'option_4' => $this->faker->sentence,
            'option_5' => $this->faker->sentence,
            'correct_answer_no' => $this->faker->randomElement([1,2,3,4,5]),
        ];
    }
}
