<?php

namespace App\Console\Commands;

use App\Jobs\ProcessDigestEmail;
use App\Models\Quiz;
use Illuminate\Console\Command;

class DailyDigest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'daily:digest';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send digest emails to users who requested to get reports of their quiz';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $quizzes = Quiz::with('author')
            ->where('digest_email', 1)
            ->cursor();

        foreach ($quizzes as $quiz) {
            ProcessDigestEmail::dispatch($quiz);
        }
    }
}
