<?php

namespace App\Actions;

use App\Models\User;
use App\Models\Workspace;
use App\Models\Membership;
use App\Models\Kpi;
use App\Models\KanbanBoard;
use App\Models\NotificationPreference;
use App\Support\CurrentWorkspace;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * First-login onboarding: give a brand-new user a working workspace with
 * roles, a seeded KPI set for their business type, a Kanban board, and
 * notification defaults. Idempotent — a user who already owns a workspace
 * is returned untouched.
 */
class ProvisionWorkspace
{
    /** Suggested KPI starter sets by business type. */
    public const KPI_TEMPLATES = [
        'Retail / Shop' => [
            ['Daily Sales', '₹', 25000, 'higher_better'],
            ['Footfall', '', 60, 'higher_better'],
            ['Avg Bill Value', '₹', 450, 'higher_better'],
            ['Repeat Customers', '', 15, 'higher_better'],
        ],
        'Services / Agency' => [
            ['Sales Calls', '', 20, 'higher_better'],
            ['Proposals Sent', '', 5, 'higher_better'],
            ['Deals Closed', '', 2, 'higher_better'],
            ['Collections', '₹', 100000, 'higher_better'],
        ],
        'Manufacturing / Distribution' => [
            ['Units Produced', '', 500, 'higher_better'],
            ['New Distributors', '', 2, 'higher_better'],
            ['Order Fulfilment', '%', 95, 'higher_better'],
            ['Wastage', '%', 3, 'lower_better'],
        ],
    ];

    public function __invoke(User $user, string $businessType = 'Services / Agency'): Workspace
    {
        if ($user->current_workspace_id) {
            return $user->currentWorkspace;
        }

        return DB::transaction(function () use ($user, $businessType) {
            $workspace = Workspace::create([
                'name'          => $user->name ? "{$user->name}'s Workspace" : 'My Business',
                'business_type' => $businessType,
                'owner_user_id' => $user->id,
                'plan'          => 'free',
                'trial_ends_at' => now()->addDays(14),
            ]);

            Membership::create([
                'workspace_id' => $workspace->id,
                'user_id'      => $user->id,
                'role'         => Membership::ROLE_OWNER,
                'joined_at'    => now(),
            ]);

            $user->update(['current_workspace_id' => $workspace->id]);

            // spatie teams mode: scope the role to this workspace.
            app(PermissionRegistrar::class)->setPermissionsTeamId($workspace->id);
            $owner = Role::findOrCreate(Membership::ROLE_OWNER, 'web');
            $user->assignRole($owner);

            // Everything below is workspace-owned — set the active workspace so the
            // BelongsToWorkspace trait auto-fills workspace_id on create.
            app(CurrentWorkspace::class)->set($workspace->id);

            foreach (self::KPI_TEMPLATES[$businessType] ?? self::KPI_TEMPLATES['Services / Agency'] as $i => [$name, $unit, $target, $dir]) {
                Kpi::create([
                    'name' => $name, 'unit' => $unit, 'direction' => $dir,
                    'target_default' => $target, 'sort_order' => $i, 'active' => true,
                ]);
            }

            $board = KanbanBoard::create(['name' => 'Execution Board']);
            foreach (['To Do', 'In Progress', 'Done'] as $i => $colName) {
                $board->columns()->create(['name' => $colName, 'position' => $i, 'workspace_id' => $workspace->id]);
            }

            NotificationPreference::create([
                'user_id'      => $user->id,
                'workspace_id' => $workspace->id,
                'channels'     => ['webpush', 'mail'],
                'enabled'      => true,
            ]);

            return $workspace;
        });
    }
}
