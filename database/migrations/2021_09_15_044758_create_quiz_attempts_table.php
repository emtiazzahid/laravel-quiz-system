<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuizAttemptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('quiz_id');
            $table->unsignedBigInteger('user_id');
            $table->tinyInteger('total_mcq')->nullable()
                ->comment('Total MCQ present on test time');
            $table->tinyInteger('total_answered_mcq')->nullable();
            $table->tinyInteger('total_correct_answer')->nullable();
            $table->float('high_score', 5,2)->nullable()
                ->comment('High score on that attempt');
            $table->boolean('status')->default(1); //1: Running or 0: completed
            $table->float('score', 5,2)->default(0);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('quiz_attempts');
    }
}
