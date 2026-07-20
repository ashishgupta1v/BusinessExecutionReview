<?php

namespace App\Models;

use App\Models\Concerns\BelongsToWorkspace;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ReviewPeriod extends Model
{
    use BelongsToWorkspace;

    public const TYPE_DAY     = 'day';
    public const TYPE_WEEK    = 'week';
    public const TYPE_MONTH   = 'month';
    public const TYPE_QUARTER = 'quarter';

    public const OPEN      = 'open';
    public const COMPLETED = 'completed';
    public const OVERDUE   = 'overdue';

    protected $fillable = [
        'workspace_id', 'user_id', 'period_type', 'period_start', 'period_end',
        'status', 'completed_at', 'reference_type', 'reference_id',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end'   => 'date',
        'completed_at' => 'datetime',
    ];

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
