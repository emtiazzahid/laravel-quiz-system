<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return response()->json([
        'name' => 'QuizAPI',
        'url' => 'https://squiz-app.netlify.app',
        'description' => 'Api built with PHP 8. Framework used Laravel',
    ]);
});


Route::get('/cache-reset', function () {
    \Illuminate\Support\Facades\Artisan::call('config:cache');
    \Illuminate\Support\Facades\Artisan::call('route:cache');
});

// --- TEMP: one-time MCQ bank seeder (batched for short max_execution_time). Delete after use. ---
Route::get('/__seed-mcq', function (\Illuminate\Http\Request $request) {
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
        $u = '?key=mcqseed-54dc0c314ddb&offset=' . $next;
        return response('<pre>inserted ' . min($next, $total) . ' / ' . $total . ' ... <a href=\"' . $u . '\">next</a></pre><meta http-equiv=\"refresh\" content=\"0;url=' . $u . '\">');
    }
    return response('<pre>DONE: seeded ' . $total . ' MCQs (author_id=' . $authorId . ')</pre>');
});
