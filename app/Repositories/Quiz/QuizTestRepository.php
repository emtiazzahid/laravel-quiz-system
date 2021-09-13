<?php

namespace App\Repositories\Quiz;

use App\Models\MCQ;
use App\Models\Quiz;

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

    /**
     * @param $id
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|null
     */
    public function getMCQList($id)
    {
        return $this->quiz->with('mcqs')
                ->findOrFail($id);
    }

    /**
     * @param $id
     * @param $request
     */
    public function processTest($id, $request)
    {
        $answers = [];
        foreach ($request->mcqs as $mcq) {
            if (isset($mcq['given_answer'])) {
                $answers[$mcq['id']] = $mcq['given_answer'];
            }
        }

        $quiz = $this->quiz->withCount('mcqs')->find($id);
        $mcqs = MCQ::whereIn('id', array_keys($answers))->get();

        $point = 0;
        foreach ($mcqs as $mcq) {
            if($mcq->correct_answer_no == $answers[$mcq->id]) {
                $point++;
            }
        };

        $total_answered_mcq = count($answers);
        $score = round((($point * 100) / $total_answered_mcq), 2);

        if ($quiz->high_score < $score) {
            $quiz->update('high_score', $score);
        }

        return [
            'total_mcq' => $quiz->mcqs_count,
            'total_answered_mcq' => $total_answered_mcq,
            'total_right_answer' => $point,
            'high_score' => $quiz->high_score,
            'score' => $score,
        ];
    }
}
