<?php

namespace App\Models\Concerns;

use App\Models\Workspace;
use App\Scopes\WorkspaceScope;
use App\Support\CurrentWorkspace;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Add to every tenant-owned model.
 *  - applies the global WorkspaceScope (auto-filter reads)
 *  - auto-fills workspace_id on create from the active workspace
 */
trait BelongsToWorkspace
{
    public static function bootBelongsToWorkspace(): void
    {
        static::addGlobalScope(new WorkspaceScope());

        static::creating(function ($model) {
            if (empty($model->workspace_id)) {
                $current = app(CurrentWorkspace::class);
                if ($current->check()) {
                    $model->workspace_id = $current->id();
                }
            }
        });
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    /** Escape hatch for cross-tenant admin/analytics queries. */
    public function scopeWithoutWorkspaceScope($query)
    {
        return $query->withoutGlobalScope(WorkspaceScope::class);
    }
}
