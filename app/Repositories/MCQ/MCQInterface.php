<?php

namespace App\Repositories\MCQ;

interface MCQInterface
{
    public function getAll($request);

    public function getById($id);

    public function create(array $attr);

    public function update($id, array $attr);

    public function delete($id);
}
