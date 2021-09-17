<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\MCQController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\QuizTestController;
use App\Http\Controllers\QuizAttemptController;
use App\Http\Controllers\ReportsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/user-profile', [AuthController::class, 'userProfile']);
});

// Authorised routes
Route::group([
    'middleware' => ['api','auth']
], function ($router) {
    Route::put('quiz/{id}/mcq', [QuizController::class,'updateMCQForQuiz']);
    Route::get('quiz/{id}/mcq', [QuizController::class,'quizWithMCQ']);
    Route::put('quiz/{id}/diggest-email', [QuizController::class,'updateDigestSettings']);
    Route::apiResource('quiz', QuizController::class);
    Route::apiResource('mcq', MCQController::class);
    Route::get('dashboard-summary', [ReportsController::class, 'dashboard']);
});

Route::group([
    'middleware' => 'api',
    'prefix' => 'pub'
], function ($router) {
    // Public routes
    Route::get('quiz', [HomeController::class,'quizzes']);
    Route::get('quiz/{id}', [HomeController::class,'quiz']);

    Route::group([
        'middleware' => ['auth']
    ], function ($router) {
        Route::get('quiz/{id}/mcq', [QuizTestController::class,'mcqList']);
        Route::post('quiz/{id}/start', [QuizTestController::class,'start']);
        Route::get('attempts', [QuizAttemptController::class,'index']);
        Route::post('attempts/{id}/complete', [QuizAttemptController::class,'complete']);
        Route::get('attempts/{id}', [QuizAttemptController::class,'result']);
    });
});


