<?php

namespace Database\Seeders;

use App\Models\MCQ;
use App\Models\Quiz;
use App\Models\QuizMCQ;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            UserTableSeeder::class,
        ]);

        Quiz::factory()->count(20)->create()->each(function($c) {
            $c->mcqs()->saveMany(
                MCQ::factory()->count(random_int(10,50))->create()
            );
        });
    }
}
