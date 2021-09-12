<?php

namespace Database\Seeders;

use App\Models\MCQ;
use App\Models\Quiz;
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

        Quiz::factory()
            ->count(50)
            ->create();

        MCQ::factory()
            ->count(50)
            ->create();
    }
}
