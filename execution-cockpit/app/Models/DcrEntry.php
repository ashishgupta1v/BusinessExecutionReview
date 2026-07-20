<?php

namespace App\Models;

use App\Models\Concerns\BelongsToWorkspace;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DcrEntry extends Model
{
    use BelongsToWorkspace;

    protected $fillable = [
        'workspace_id', 'user_id', 'entry_date', 'reflection_note',
        'moved_needle', 'submitted_at',
    ];

    protected $casts = [
        'entry_date'   => 'date',
        'moved_needle' => 'boolean',
        'submitted_at' => 'datetime',
    ];

    public function tasks(): HasMany
    {
        return $this->hasMany(DcrTask::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
