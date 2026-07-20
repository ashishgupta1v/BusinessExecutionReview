<?php

namespace App\Models;

use App\Models\Concerns\BelongsToWorkspace;
use Illuminate\Database\Eloquent\Model;

class WeeklyReview extends Model
{
    use BelongsToWorkspace;

    protected $fillable = [
        'workspace_id', 'user_id', 'iso_year', 'iso_week',
        'planned_goals', 'achieved', 'major_wins', 'challenges',
        'moved_needle_answer', 'next_week_focus', 'status', 'completed_at',
    ];

    protected $casts = [
        'iso_year'        => 'integer',
        'iso_week'        => 'integer',
        'planned_goals'   => 'array',
        'achieved'        => 'array',
        'next_week_focus' => 'array',
        'completed_at'    => 'datetime',
    ];
}
