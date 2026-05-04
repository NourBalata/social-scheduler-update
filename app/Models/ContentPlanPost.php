<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContentPlanPost extends Model
{
    protected $fillable = [
        'content_plan_id', 'page_name', 'content',
        'post_type', 'scheduled_at', 'status'
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    public function plan()
    {
        return $this->belongsTo(ContentPlan::class);
    }
}