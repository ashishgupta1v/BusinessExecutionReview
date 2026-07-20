<?php

use Illuminate\Support\Facades\Schedule;
use App\Console\Commands\DispatchReminders;

/*
 | Reminder scheduling (Laravel 12 style — schedule lives in routes/console.php).
 |
 | Server crontab has exactly ONE line:
 |   * * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1
 |
 | We run the dispatcher hourly (top of every hour). Each run resolves every
 | user's LOCAL time and fires only the reminders whose local hour matches.
 | withoutOverlapping() stops a slow run from colliding with the next tick.
 |
 | For minute-precision reminder times, change ->hourly() to ->everyFifteenMinutes()
 | and compare H:i instead of H in DispatchReminders::hourMatches().
 */
Schedule::command(DispatchReminders::class)
    ->hourly()
    ->withoutOverlapping()
    ->onOneServer()
    ->runInBackground();

// Cadence engine: generate the day/week/month/quarter periods for active users,
// then flag any past-window periods still "open" as "overdue" (feeds streak-nudge logic).
Schedule::command('cadence:generate')->dailyAt('00:05')->onOneServer();
Schedule::command('cadence:mark-overdue')->dailyAt('00:15')->onOneServer();
