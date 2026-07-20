<?php

namespace App\Console\Commands;

/**
 * Deprecated shim. The active `cadence:mark-overdue` command is MarkOverdue.php.
 * This class intentionally does NOT extend Illuminate\Console\Command, so Laravel's
 * command discovery skips it — no duplicate-signature clash at boot.
 */
final class MarkOverduePeriods
{
    // intentionally empty
}
