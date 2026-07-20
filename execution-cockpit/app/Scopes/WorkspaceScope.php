<?php

namespace App\Scopes;

use App\Support\CurrentWorkspace;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Filters every tenant-owned model to the active workspace.
 * If no workspace is set (e.g. console, or pre-auth), the scope is a no-op —
 * guard sensitive queries explicitly rather than relying on an empty result.
 */
class WorkspaceScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $current = app(CurrentWorkspace::class);

        if ($current->check()) {
            $builder->where(
                $model->getTable().'.workspace_id',
                $current->id()
            );
        }
    }
}
