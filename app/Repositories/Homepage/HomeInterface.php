<?php

namespace App\Repositories\Homepage;

interface HomeInterface
{
    public function getQuizzes($request);

    public function getQuizById($id);
}
