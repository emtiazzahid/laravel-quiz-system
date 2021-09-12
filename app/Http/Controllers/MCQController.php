<?php

namespace App\Http\Controllers;

use App\Http\Requests\MCQRequest;
use App\Http\Resources\MCQResource;
use App\Repositories\MCQ\MCQInterface;
use Illuminate\Http\Request;

class MCQController extends BaseController
{
    private $mcq;

    /**
     * @param MCQInterface $mcq
     */
    public function __construct(MCQInterface $mcq)
    {
        $this->mcq = $mcq;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        return MCQResource::collection(
            $this->mcq->getAll($request)
        );
    }

    /**
     * @param MCQRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(MCQRequest $request)
    {
        $data = $this->mcq->create($request->all());

        return $this->sendResponse($data, 'Data Insert Successfully');
    }

    /**
     * @param $id
     * @return mixed
     */
    public function show($id)
    {
        return $this->mcq->getById($id);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function edit($id)
    {
        return $this->mcq->getById($id);
    }

    /**
     * @param $id
     * @param MCQRequest $request
     * @return mixed
     */
    public function update($id, MCQRequest $request)
    {
        return $this->mcq->update($id, $request->all());
    }

    /**
     * @param $id
     * @return mixed
     */
    public function destroy($id)
    {
        return $this->mcq->delete($id);
    }
}
