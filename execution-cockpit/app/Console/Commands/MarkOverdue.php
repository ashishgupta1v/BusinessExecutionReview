<?php

namespace App\Console\Commands;

use App\Models\ReviewPeriod;
use Illuminate\Console\Command;

class MarkOverdue extends Command
{
    protected $signature = 'cadence:mark-overdue';
    protected $description = 'Mark past-end review periods as overdue';

    public function handle(): int
    {
        $count = ReviewPeriod::where('status', 'open')
            ->where('period_end', '<', today()->toDateString())
            ->update(['status' => 'overdue']);

        $this->info("cadence:mark-overdue complete — {$count} review periods marked as overdue.");
        return self::SUCCESS;
    }
}
