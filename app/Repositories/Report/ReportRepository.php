<?php

namespace App\Repositories\Report;

use App\Enums\QuizAttemptStatus;
use App\Models\MCQ;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Report;
class ReportRepository implements ReportInterface
{
    /**
     * @return array
     */
    public function dashboardData()
    {
        $user_id = auth()->user()->id;

        $quiz_ids = Quiz::where('author_id', $user_id)->pluck('id')->toArray();;
        $total_mcq = MCQ::where('author_id', $user_id)->count();

        $attempts = QuizAttempt::whereIn('quiz_id', $quiz_ids);
        $total_attendant = $attempts->count();
        $total_running_exams = $attempts->where('status', QuizAttemptStatus::$RUNNING)->count();

        // Stats as string is vuetify demand
        return [
            'total_quiz' => (string) count($quiz_ids),
            'total_mcq' => (string) $total_mcq,
            'total_attendant'=> (string) $total_attendant,
            'total_running_exams'=> (string) $total_running_exams,
        ];
    }
}
