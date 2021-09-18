<?php

namespace App\Jobs;

use App\Mail\DailyDigest;
use App\Models\Quiz;
use App\Repositories\Quiz\QuizTestRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class ProcessDigestEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $quiz;

    private $quizTest;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($quiz)
    {
        $this->quiz = $quiz;
        $this->quizTest = new QuizTestRepository(new Quiz());
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $report = $this->quizTest->digestReport($this->quiz->id);
        if (!$report) {
            return;
        }

        Mail::to($this->quiz->author->email)
            ->send(new DailyDigest($report));
    }
}
