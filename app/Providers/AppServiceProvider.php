<?php

namespace App\Providers;

use App\Repositories\Homepage\HomeInterface;
use App\Repositories\Homepage\HomeRepository;
use App\Repositories\MCQ\MCQInterface;
use App\Repositories\MCQ\MCQRepository;
use App\Repositories\Quiz\QuizInterface;
use App\Repositories\Quiz\QuizRepository;
use App\Repositories\Quiz\QuizTestInterface;
use App\Repositories\Quiz\QuizTestRepository;
use App\Repositories\Report\ReportInterface;
use App\Repositories\Report\ReportRepository;
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
        $this->app->singleton(HomeInterface::class, HomeRepository::class);
        $this->app->singleton(QuizTestInterface::class, QuizTestRepository::class);
        $this->app->singleton(ReportInterface::class, ReportRepository::class);
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
