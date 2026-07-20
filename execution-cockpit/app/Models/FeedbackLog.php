<?php

namespace App\Models;

use App\Models\Concerns\BelongsToWorkspace;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedbackLog extends Model
{
    use BelongsToWorkspace;

    protected $fillable = [
        'workspace_id', 'created_by', 'feedback_date', 'type', 'body',
        'action_taken', 'assigned_to_user_id', 'assignee_name', 'status', 'resolved_at',
    ];

    protected $casts = [
        'feedback_date' => 'date',
        'resolved_at'   => 'datetime',
    ];

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }
}
