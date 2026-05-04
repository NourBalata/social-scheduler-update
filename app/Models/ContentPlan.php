<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContentPlan extends Model
{
    protected $fillable = [
        'user_id', 'page_name', 'business_type',
        'audience', 'tone', 'language',
        'start_date', 'end_date', 'posts_per_week', 'status'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function posts()
    {
        return $this->hasMany(ContentPlanPost::class);
    }
}