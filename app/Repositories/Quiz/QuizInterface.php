<?php

namespace App\Repositories\Quiz;

interface QuizInterface
{
    public function getAll($request);

    public function getById($id);

    public function create(array $attr);

    public function update($id, array $attr);

    public function delete($id);
}
