<?php

namespace App\Models;

use App\Models\Concerns\BelongsToWorkspace;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonthlyReview extends Model
{
    use BelongsToWorkspace;

    protected $fillable = [
        'workspace_id', 'user_id', 'year', 'month', 'summary',
        'wins', 'challenges', 'kpi_snapshot', 'actions', 'status', 'completed_at',
    ];

    protected $casts = [
        'year'         => 'integer',
        'month'        => 'integer',
        'kpi_snapshot' => 'array',
        'actions'      => 'array',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
