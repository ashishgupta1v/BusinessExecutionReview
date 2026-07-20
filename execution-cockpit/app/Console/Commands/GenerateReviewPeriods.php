<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\CadenceEngine;
use App\Support\CurrentWorkspace;
use Illuminate\Console\Command;

/**
 * Creates today's day period plus the current week/month/quarter periods for
 * every active user, in their current workspace. Scheduled daily (routes/console.php).
 * Idempotent — firstOrCreate means re-runs are safe.
 */
class GenerateReviewPeriods extends Command
{
    protected $signature = 'cadence:generate';
    protected $description = 'Generate day/week/month/quarter review periods for active users';

    public function handle(CadenceEngine $engine): int
    {
        $count = 0;

        User::whereNotNull('current_workspace_id')
            ->chunkById(200, function ($users) use ($engine, &$count) {
                foreach ($users as $user) {
                    app(CurrentWorkspace::class)->set((int) $user->current_workspace_id);
                    $engine->generateFor($user);
                    $count++;
                }
            });

        $this->info("cadence:generate — periods ensured for {$count} user(s)");
        return self::SUCCESS;
    }
}
