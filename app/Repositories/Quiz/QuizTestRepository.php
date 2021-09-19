<?php

namespace App\Repositories\Quiz;

use App\Enums\QuizAttemptStatus;
use App\Models\MCQ;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class QuizTestRepository implements QuizTestInterface
{
    /**
     * @var Quiz
     */
    private $quiz;

    /**
     * @param Quiz $quiz
     */
    public function __construct(Quiz $quiz)
    {
        $this->quiz = $quiz;
    }

    public function getAllAttempts($request)
    {
        return QuizAttempt::where('user_id', auth()->user()->id)
            ->orderBy('id', 'desc')
            ->paginate($request->perPage);
    }

    /**
     * @param $id
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|null
     */
    public function getMCQList($id)
    {
        return $this->quiz->with(['mcqs' => function ($q) {
            return $q->inRandomOrder(); // Show MCQ lists in random order while quiz running
        }])->findOrFail($id);
    }

    /**
     * @param $id
     * @param $request
     */
    public function processTest($attempt_id, $answered_mcqs = [])
    {
        $quiz = $this->findQuizByAttempt($attempt_id);

        $answers = $this->filterAnswerSheet($answered_mcqs);

        $total_answered_mcq = count($answers);
        $total_correct_answer = 0;
        $score = 0;

        if ($total_answered_mcq) {
            $total_correct_answer = $this->findTotalCorrectAnswer($answers);

            $score = $this->calculateScore($total_correct_answer, $quiz->mcqs_count);

            if ($quiz->high_score < $score) {
                $quiz->update(['high_score' => $score]);
            }
        }

        return $this->updateQuizAttemptRecord([
            'total_mcq' => $quiz->mcqs_count,
            'total_answered_mcq' => $total_answered_mcq,
            'total_correct_answer' => $total_correct_answer,
            'high_score' => $quiz->high_score,
            'score' => $score,
            'status' => QuizAttemptStatus::$COMPLETED,
        ]);
    }

    /**
     * @param $id
     * @return array
     */
    public function startQuiz($id, $attempt_id = null)
    {
        if ($attempt_id) {
            $attempt = QuizAttempt::where('user_id', auth()->user()->id)->find($attempt_id);
        } else {
            // Check previously active quiz attempt for this user
            $attempt = QuizAttempt::with('quiz')
                ->where([
                    ['quiz_id', $id],
                    ['user_id', auth()->user()->id],
                    ['status', QuizAttemptStatus::$RUNNING]
                ])->first();
        }

        if ($attempt) {
            // Check if quiz limit time already over
            $valid_time = Carbon::parse($attempt->created_at)->addSeconds($attempt->quiz->time_limit);
            $current_time = Carbon::now();
            if ($valid_time > $current_time) {
                $remaining_time = $current_time->diffInSeconds($valid_time);
            } else {
                // Process the test because time limit exceed
                $this->processTest($id);
                $remaining_time = null;
            }
        } else {
            // Make fresh quiz test and pass it's remaining time
            $attempt = QuizAttempt::create([
                'quiz_id' => $id,
                'user_id' => auth()->user()->id
            ]);

            $remaining_time = $this->quiz->find($id)->time_limit;
        }

        return [
            'remaining_time' => $remaining_time,
            'attempt_id' => $attempt->id
        ];
    }

    /**
     * @return mixed
     */
    public function getResult($id)
    {
        return QuizAttempt::with('quiz')
            ->where([
                ['id', $id],
                ['user_id', auth()->user()->id] // Make sure api fetching their own result
            ])->first();
    }

    public function digestReport($id)
    {
        // Search quiz attempts from yesterday
        $quiz = $this->quiz->whereHas('quiz_attempts', function ($q) {
            $q->whereDate('created_at', now()->subDays(1));
        })->with('quiz_attempts.attendee')->find($id);
        if (!$quiz) {
            return false;
        }

        $attempts = [];
        foreach ($quiz->quiz_attempts as $attempt) {
            $attempts[$attempt->attendee->id][] = $attempt->toArray();
        }

        $result = [];
        foreach ($attempts as $attempt) {
            $marks = $this->getAvgMinMax(array_column($attempt, 'score'));
            $result[] = array_merge([
                'user_name' => Arr::get($attempt,'0.attendee.name'),
                'user_email' => Arr::get($attempt,'0.attendee.email'),
                'number_of_attempt' => count($attempt),
            ], $marks);
        }

        return [
            'quiz_title' => $quiz->title,
            'date' => now()->subDays(1)->format('Y-m-d'),
            'users' => $result,
        ];
    }

    /**
     * @param $total_correct_answer
     * @param $total_mcq
     * @return float
     */
    private function calculateScore($total_correct_answer, $total_mcq)
    {
        return round((($total_correct_answer * 100) / $total_mcq), 2);
    }

    /**
     * @param $data
     * @return mixed
     */
    private function updateQuizAttemptRecord($data)
    {
        return QuizAttempt::where(
            'user_id', auth()->user()->id
        )
            ->orderBy('id', 'desc')
            ->take(1)
            ->first()
            ->update($data);
    }

    /**
     * @param $scores
     * @return array
     */
    private function getAvgMinMax($scores): array
    {
        return [
            'max' => max($scores),
            'min' => min($scores),
            'avg' => array_sum($scores) / count($scores),
        ];
    }

    /**
     * @param $attempt_id
     * @return mixed
     */
    private function findQuizByAttempt($attempt_id)
    {
        $attempt = QuizAttempt::where('user_id', auth()->user()->id)
            ->findOrFail($attempt_id);

        return $this->quiz->withCount('mcqs')->find($attempt->quiz_id);
    }

    /**
     * @param $answered_mcqs
     * @return array
     */
    private function filterAnswerSheet($answered_mcqs): array
    {
        $answers = [];
        foreach ($answered_mcqs as $mcq) {
            if (isset($mcq['given_answer'])) {
                $answers[$mcq['id']] = $mcq['given_answer'];
            }
        }

        return $answers;
    }

    /**
     * @param array $answers
     * @return int
     */
    private function findTotalCorrectAnswer(array $answers)
    {
        $total_correct_answer = 0;
        $mcqs = MCQ::whereIn('id', array_keys($answers))->get();

        foreach ($mcqs as $mcq) {
            if($mcq->correct_answer_no == $answers[$mcq->id]) {
                $total_correct_answer++;
            }
        };

        return $total_correct_answer;
    }
}
