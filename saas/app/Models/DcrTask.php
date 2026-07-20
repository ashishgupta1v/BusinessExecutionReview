<?php

namespace App\Models;

use App\Models\Concerns\BelongsToWorkspace;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DcrTask extends Model
{
    use BelongsToWorkspace;

    protected $fillable = [
        'dcr_entry_id', 'workspace_id', 'type', 'title', 'is_measurable',
        'new_deadline', 'kanban_card_id', 'position',
    ];

    protected $casts = [
        'is_measurable' => 'boolean',
        'new_deadline'  => 'date',
        'position'      => 'integer',
    ];

    public const TYPE_COMPLETED = 'completed';
    public const TYPE_PENDING   = 'pending';
    public const TYPE_PRIORITY  = 'priority';

    public function entry(): BelongsTo
    {
        return $this->belongsTo(DcrEntry::class, 'dcr_entry_id');
    }

    public function kanbanCard(): BelongsTo
    {
        return $this->belongsTo(KanbanCard::class, 'kanban_card_id');
    }
}
