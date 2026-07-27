<?php

namespace Database\Seeders;

use App\Models\MCQ;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Demo data so the app looks alive:
 *  - a working demo login (user_1@mail.com / password) that owns MCQs & quizzes
 *  - public quizzes (so the homepage shows content)
 *  - a couple of extra attendees and completed quiz attempts
 *
 * Idempotent: re-running wipes and rebuilds user_1's demo content.
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $now = now()->toDateTimeString();

        $u1 = User::updateOrCreate(['email' => 'user_1@mail.com'], ['name' => 'Demo User', 'password' => Hash::make('password')]);
        $u2 = User::firstOrCreate(['email' => 'user_2@mail.com'], ['name' => 'Alex Carter', 'password' => Hash::make('password')]);
        $u3 = User::firstOrCreate(['email' => 'user_3@mail.com'], ['name' => 'Sam Rivera', 'password' => Hash::make('password')]);

        // ---- idempotent reset of user_1's demo content ----
        $oldQuizIds = Quiz::where('author_id', $u1->id)->pluck('id');
        DB::table('quiz_attempts')->whereIn('quiz_id', $oldQuizIds)->delete();
        DB::table('quiz_m_c_q_s')->whereIn('quiz_id', $oldQuizIds)->delete();
        Quiz::where('author_id', $u1->id)->delete();
        MCQ::where('author_id', $u1->id)->delete();

        // ---- MCQs owned by user_1 (curated multi-sector set) ----
        $rows = [];
        foreach (McqSeeder::curated() as [$q, $opts, $correct]) {
            $opts = array_values($opts);
            $rows[] = [
                'author_id' => $u1->id,
                'question' => $q,
                'option_1' => (string) ($opts[0] ?? ''),
                'option_2' => (string) ($opts[1] ?? ''),
                'option_3' => isset($opts[2]) ? (string) $opts[2] : null,
                'option_4' => isset($opts[3]) ? (string) $opts[3] : null,
                'option_5' => isset($opts[4]) ? (string) $opts[4] : null,
                'correct_answer_no' => $correct,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        DB::table('m_c_q_s')->insert($rows);
        $mcqIds = MCQ::where('author_id', $u1->id)->orderBy('id')->pluck('id')->all();
        $n = count($mcqIds);

        // ---- public quizzes built from those MCQs ----
        $quizzes = [
            ['General Knowledge Starter', 'A quick warm-up across a bit of everything.'],
            ['World Geography', 'Capitals, continents and places around the globe.'],
            ['Science Essentials', 'Physics, chemistry and biology basics.'],
            ['Tech & Programming', 'Fundamentals every developer should know.'],
            ['History Highlights', 'People and events that shaped the world.'],
            ['Sports Trivia', 'From football to the Olympics.'],
            ['Language & Words', 'Grammar, synonyms and vocabulary.'],
            ['Mixed Challenge', 'A harder mix to test your range.'],
        ];
        $attendees = [$u1->id, $u2->id, $u3->id];
        $per = 10;

        foreach ($quizzes as $qi => [$title, $desc]) {
            $slice = [];
            for ($k = 0; $k < $per && $n > 0; $k++) {
                $slice[] = $mcqIds[($qi * 7 + $k) % $n];
            }
            $slice = array_values(array_unique($slice));
            $total = count($slice);

            $quizId = DB::table('quizzes')->insertGetId([
                'author_id' => $u1->id,
                'title' => $title,
                'description' => $desc,
                'time_limit' => 600,
                'high_score' => 0,
                'digest_email' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach ($slice as $mid) {
                DB::table('quiz_m_c_q_s')->insert(['quiz_id' => $quizId, 'mcq_id' => $mid]);
            }

            // completed attempts with deterministic, varied scores
            $best = 0.0;
            $bestUser = null;
            foreach ($attendees as $ai => $uid) {
                $correct = (int) round($total * (0.5 + 0.12 * (($qi + $ai) % 4)));
                $correct = max(1, min($total, $correct));
                $score = $total > 0 ? round($correct / $total * 100, 2) : 0;
                if ($score > $best) {
                    $best = $score;
                    $bestUser = $uid;
                }
                DB::table('quiz_attempts')->insert([
                    'quiz_id' => $quizId,
                    'user_id' => $uid,
                    'total_mcq' => $total,
                    'total_answered_mcq' => $total,
                    'total_correct_answer' => $correct,
                    'high_score' => $score,
                    'status' => 0,
                    'score' => $score,
                    'created_at' => $now,
                ]);
            }
            DB::table('quizzes')->where('id', $quizId)->update(['high_score' => $best, 'high_scorer_id' => $bestUser]);
        }

        if ($this->command) {
            $this->command->info('Demo seeded: user_1@mail.com ('.$n.' MCQs, '.count($quizzes).' public quizzes + attempts).');
        }
    }
}
