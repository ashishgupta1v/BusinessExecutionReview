<?php

namespace App\Models;

use App\Models\Concerns\BelongsToWorkspace;
use Illuminate\Database\Eloquent\Model;

class DisciplineChecklist extends Model
{
    use BelongsToWorkspace;

    protected $fillable = [
        'workspace_id', 'user_id', 'check_date',
        'rule_of_5_count', 'two_minute_done', 'time_blocked', 'items',
    ];

    protected $casts = [
        'check_date'      => 'date',
        'rule_of_5_count' => 'integer',
        'two_minute_done' => 'boolean',
        'time_blocked'    => 'boolean',
        'items'           => 'array',
    ];
}
