<?php

namespace App\Http\Controllers;

use App\Http\Requests\QuizRequest;
use App\Http\Resources\QuizResource;
use App\Repositories\Quiz\QuizInterface;
use Illuminate\Http\Request;

class QuizController extends BaseController
{
    private $quiz;

    public function __construct(QuizInterface $quiz){
        $this->quiz = $quiz;
    }

    public function index(Request $request)
    {
        return QuizResource::collection(
            $this->quiz->getAll($request)
        );
    }

    public function store(QuizRequest $request)
    {
        $data = $this->quiz->create($request->all());

        return $this->sendResponse($data, 'Data Insert Successfully');
    }

    public function show($id)
    {
        return new QuizResource(
            $this->quiz->getById($id)
        );
    }

    public function edit($id)
    {
        return $this->quiz->getById($id);
    }

    public function update($id, QuizRequest $request)
    {
        return $this->quiz->update($id, $request->all());
    }

    public function destroy($id)
    {
        return $this->quiz->delete($id);
    }

    public function quizWithMCQ($id)
    {
        return $this->quiz->getMCQ($id);
    }

    public function updateMCQForQuiz($id, Request $request)
    {
        $request->validate([
            'mcq_ids' => 'required|array|min:1'
        ], [
            'At least one mcq is required'
        ]);

        $this->quiz->updateMCQ($id, $request->mcq_ids);

        return $this->sendResponse('', 'Data Updated Successfully');
    }
}
