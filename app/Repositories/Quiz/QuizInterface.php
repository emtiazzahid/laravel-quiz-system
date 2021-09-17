<?php

namespace App\Repositories\Quiz;

interface QuizInterface
{
    public function getAll($request);

    public function getById($id);

    public function create(array $attr);

    public function update($id, array $attr);

    public function updateDigestSettings($id, bool $status);

    public function delete($id);

    public function getMCQ($id);

    public function updateMCQ($id, array $mcq_ids);
}
