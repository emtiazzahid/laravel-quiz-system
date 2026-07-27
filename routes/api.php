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
    'middleware' => 'auth:sanctum'
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
        'middleware' => 'auth:sanctum'
    ], function ($router) {
        Route::get('quiz/{id}/mcq', [QuizTestController::class,'mcqList']);
        Route::post('quiz/{id}/start', [QuizTestController::class,'start']);
        Route::get('attempts', [QuizAttemptController::class,'index']);
        Route::post('attempts/{id}/complete', [QuizAttemptController::class,'complete']);
        Route::get('attempts/{id}', [QuizAttemptController::class,'result']);
    });
});

// --- TEMP: one-time MCQ bank seeder (batched). Reachable at /api/__seed-mcq . Delete after use. ---
Route::get('__seed-mcq', function (\Illuminate\Http\Request $request) {
    abort_unless($request->query('key') === 'mcqseed-54dc0c314ddb', 403, 'Forbidden');
    $authorId = \Database\Seeders\McqSeeder::ensureAuthor();
    $rows = \Database\Seeders\McqSeeder::rows($authorId);
    $total = count($rows);
    $offset = max(0, (int) $request->query('offset', 0));
    $batch = 300;
    if ($offset === 0) {
        \Illuminate\Support\Facades\DB::table('m_c_q_s')->where('author_id', $authorId)->delete();
    }
    $slice = array_slice($rows, $offset, $batch);
    if ($slice) {
        \Illuminate\Support\Facades\DB::table('m_c_q_s')->insert($slice);
    }
    $next = $offset + $batch;
    if ($next < $total) {
        $u = '/api/__seed-mcq?key=mcqseed-54dc0c314ddb&offset=' . $next;
        return response('<pre>inserted ' . min($next, $total) . ' / ' . $total . '</pre><meta http-equiv="refresh" content="0;url=' . $u . '">');
    }
    return response('<pre>DONE: seeded ' . $total . ' MCQs (author_id=' . $authorId . ')</pre>');
});
