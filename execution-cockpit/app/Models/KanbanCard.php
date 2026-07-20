<?php

namespace App\Models;

use App\Models\Concerns\BelongsToWorkspace;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KanbanCard extends Model
{
    use BelongsToWorkspace;

    protected $fillable = [
        'column_id', 'board_id', 'workspace_id', 'title', 'description',
        'position', 'entered_column_at', 'due_date', 'created_by',
    ];

    protected $casts = [
        'position'          => 'integer',
        'entered_column_at' => 'datetime',
        'due_date'          => 'date',
    ];

    /** Stuck in its column longer than the workspace's stale threshold. */
    public function isStale(int $staleDays = 3): bool
    {
        return $this->entered_column_at
            && $this->entered_column_at->diffInDays(now()) > $staleDays;
    }

    public function column(): BelongsTo
    {
        return $this->belongsTo(KanbanColumn::class, 'column_id');
    }

    public function board(): BelongsTo
    {
        return $this->belongsTo(KanbanBoard::class, 'board_id');
    }
}
