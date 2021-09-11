<?php

namespace App\Http\Controllers;

use App\Http\Requests\MCQRequest;
use App\Http\Resources\MCQResource;
use App\Repositories\MCQ\MCQInterface;
use Illuminate\Http\Request;

class MCQController extends BaseController
{
    private $mcq;

    public function __construct(MCQInterface $mcq){
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

    public function store(MCQRequest $request)
    {
        $data = $this->mcq->create($request->all());

        return $this->sendResponse($data, 'Data Insert Successfully');
    }

    public function show($id)
    {
        return $this->mcq->getById($id);
    }

    public function edit($id)
    {
        return $this->mcq->getById($id);
    }

    public function update($id, MCQRequest $request)
    {
        return $this->mcq->update($id, $request->all());
    }

    public function destroy($id)
    {
        return $this->mcq->delete($id);
    }
}
