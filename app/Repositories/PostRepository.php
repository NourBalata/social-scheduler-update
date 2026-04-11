<?php
namespace App\Repositories;

use App\Models\ScheduledPost;

class PostRepository extends BaseRepository
{
    public function __construct(ScheduledPost $model)
    {
        parent::__construct($model);
    }

    public function getPendingPosts()
    {
        return $this->model->ready()->with('facebookPage')->get();
    }
}