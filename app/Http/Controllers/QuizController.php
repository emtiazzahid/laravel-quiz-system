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

    /**
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        return QuizResource::collection(
            $this->quiz->getAll($request)
        );
    }

    /**
     * @param QuizRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(QuizRequest $request)
    {
        $data = $this->quiz->create($request->all());

        return $this->sendResponse($data, 'Data Insert Successfully');
    }

    /**
     * @param $id
     * @return QuizResource
     */
    public function show($id)
    {
        return new QuizResource(
            $this->quiz->getById($id)
        );
    }

    /**
     * @param $id
     * @return mixed
     */
    public function edit($id)
    {
        return $this->quiz->getById($id);
    }

    /**
     * @param $id
     * @param QuizRequest $request
     * @return mixed
     */
    public function update($id, QuizRequest $request)
    {
        $this->quiz->update($id, $request->all());

        return $this->sendResponse('', 'Data updated Successfully');
    }

    /**
     * @param $id
     * @param Request $request
     * @return mixed
     */
    public function updateDigestSettings($id, Request $request)
    {
        $request->validate([
            'status' => 'required|bool'
        ]);

        $this->quiz->updateDigestSettings($id, $request->status);

        return $this->sendResponse('', 'Status Update Successfully');
    }

    /**
     * @param $id
     * @return mixed
     */
    public function destroy($id)
    {
         $this->quiz->delete($id);

         return $this->sendResponse('', 'Data deleted Successfully');
    }

    /**
     * @param $id
     * @return mixed
     */
    public function quizWithMCQ($id)
    {
        return $this->quiz->getMCQ($id);
    }

    /**
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
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
