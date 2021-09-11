<?php

namespace App\Providers;

use App\Repositories\MCQ\MCQInterface;
use App\Repositories\MCQ\MCQRepository;
use App\Repositories\Quiz\QuizInterface;
use App\Repositories\Quiz\QuizRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(QuizInterface::class, QuizRepository::class);
        $this->app->singleton(MCQInterface::class, MCQRepository::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
