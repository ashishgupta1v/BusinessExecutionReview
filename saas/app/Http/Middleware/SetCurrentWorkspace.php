<?php

namespace App\Http\Middleware;

use App\Support\CurrentWorkspace;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the active workspace for the authenticated user and makes it
 * available to the global scope. Also syncs spatie/laravel-permission's
 * active team id so per-workspace roles resolve correctly.
 *
 * Register in bootstrap/app.php:
 *   $middleware->alias(['workspace' => \App\Http\Middleware\SetCurrentWorkspace::class]);
 * then attach 'workspace' to authenticated route groups.
 */
class SetCurrentWorkspace
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            $workspaceId = $user->current_workspace_id
                ?? $user->memberships()->value('workspace_id');

            if ($workspaceId) {
                // Authorization: ensure the user actually belongs to it.
                $isMember = $user->memberships()
                    ->where('workspace_id', $workspaceId)
                    ->exists();

                if ($isMember) {
                    app(CurrentWorkspace::class)->set((int) $workspaceId);

                    // spatie teams mode: scope roles/permissions to this workspace.
                    // Reset relation cache when switching mid-request (documented gotcha).
                    app(\Spatie\Permission\PermissionRegistrar::class)
                        ->setPermissionsTeamId($workspaceId);
                    $user->unsetRelation('roles')->unsetRelation('permissions');
                }
            }
        }

        return $next($request);
    }
}
