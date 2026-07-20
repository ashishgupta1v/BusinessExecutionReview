<?php

namespace App\Console\Commands;

use App\Services\CadenceEngine;
use Illuminate\Console\Command;

/**
 * Flags every past-window period still "open" as "overdue".
 * Scheduled nightly (routes/console.php). Cross-workspace — the engine bypasses
 * the workspace scope for this sweep.
 */
class MarkOverduePeriods extends Command
{
    protected $signature = 'cadence:mark-overdue';
    protected $description = 'Mark past-due review periods as overdue';

    public function handle(CadenceEngine $engine): int
    {
        $n = $engine->markOverdue();
        $this->info("cadence:mark-overdue — {$n} period(s) flagged overdue");
        return self::SUCCESS;
    }
}
