<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\DcrEntry;
use App\Notifications\DcrReminder;
use App\Notifications\WeeklyReviewReminder;
use App\Notifications\MonthlyDeepDiveReminder;
use App\Support\CurrentWorkspace;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Per-user, timezone-safe reminder dispatcher.
 *
 * Scheduling model (DST-safe):
 *   - ONE cron entry runs `php artisan schedule:run` every minute.
 *   - The scheduler runs THIS command hourly with withoutOverlapping().
 *   - We compute each user's LOCAL time with Carbon::now($user->timezone) and
 *     fire when the local hour matches their preference. We do NOT put a
 *     ->timezone() on the schedule entry — Laravel's docs warn DST shifts can
 *     make timezone'd schedules double- or skip-fire.
 *
 * Idempotency: a per-user-per-period cache key prevents a retry or an extra
 * hourly tick from sending twice.
 */
class DispatchReminders extends Command
{
    protected $signature = 'reminders:dispatch {--now= : Override "now" (UTC ISO) for testing}';
    protected $description = 'Dispatch DCR / weekly / monthly reminders in each user\'s local timezone';

    public function handle(): int
    {
        $utcNow = $this->option('now') ? Carbon::parse($this->option('now'), 'UTC') : Carbon::now('UTC');
        $sent = 0;

        User::with(['notificationPreference', 'currentWorkspace'])
            ->whereHas('notificationPreference', fn ($q) => $q->where('enabled', true))
            ->chunkById(200, function ($users) use ($utcNow, &$sent) {
                foreach ($users as $user) {
                    $sent += $this->forUser($user, $utcNow);
                }
            });

        $this->info("reminders:dispatch complete — {$sent} notification(s) queued at {$utcNow->toIso8601String()}");
        return self::SUCCESS;
    }

    protected function forUser(User $user, Carbon $utcNow): int
    {
        $pref  = $user->notificationPreference;
        $ws    = $user->currentWorkspace;
        if (! $pref || ! $ws) return 0;

        $local = $utcNow->copy()->setTimezone($user->timezone ?? 'Asia/Kolkata');
        $sent  = 0;

        // --- daily DCR reminder ---
        if ($this->hourMatches($local, $pref->dcr_reminder_time)) {
            $key = "reminded:dcr:{$user->id}:{$local->toDateString()}";
            if ($this->once($key) && ! $this->dcrFiledToday($user, $ws->id, $local)) {
                $user->notify(new DcrReminder($ws->name, $this->currentStreak($user, $ws->id, $local)));
                $sent++;
            }
        }

        // --- weekly review reminder ---
        if ($local->dayOfWeekIso === (int) $pref->weekly_reminder_dow
            && $this->hourMatches($local, $pref->weekly_reminder_time)) {
            $key = "reminded:weekly:{$user->id}:{$local->year}-{$local->weekOfYear}";
            if ($this->once($key) && class_exists(WeeklyReviewReminder::class)) {
                $user->notify(new WeeklyReviewReminder($ws->name));
                $sent++;
            }
        }

        // --- monthly deep-dive reminder ---
        if ($local->day === (int) $pref->monthly_reminder_dom
            && $this->hourMatches($local, $pref->monthly_reminder_time)) {
            $key = "reminded:monthly:{$user->id}:{$local->format('Y-m')}";
            if ($this->once($key) && class_exists(MonthlyDeepDiveReminder::class)) {
                $user->notify(new MonthlyDeepDiveReminder($ws->name));
                $sent++;
            }
        }

        return $sent;
    }

    /** True when the local hour equals the preference hour (minute precision comes from cron cadence). */
    protected function hourMatches(Carbon $local, $prefTime): bool
    {
        return $local->format('H') === Carbon::parse($prefTime)->format('H');
    }

    /** Claim an idempotency key for ~26h so the same period never double-fires. */
    protected function once(string $key): bool
    {
        return Cache::add($key, 1, now()->addHours(26));
    }

    protected function dcrFiledToday(User $user, int $workspaceId, Carbon $local): bool
    {
        app(CurrentWorkspace::class)->set($workspaceId);
        return DcrEntry::where('user_id', $user->id)
            ->whereDate('entry_date', $local->toDateString())
            ->exists();
    }

    protected function currentStreak(User $user, int $workspaceId, Carbon $local): int
    {
        app(CurrentWorkspace::class)->set($workspaceId);
        $dates = DcrEntry::where('user_id', $user->id)
            ->orderByDesc('entry_date')->pluck('entry_date')
            ->map(fn ($d) => Carbon::parse($d)->toDateString())->all();

        $streak = 0;
        $cursor = $local->copy()->subDay(); // yesterday — today isn't filed yet at reminder time
        foreach ($dates as $d) {
            if ($d === $cursor->toDateString()) { $streak++; $cursor->subDay(); }
            elseif ($d < $cursor->toDateString()) break;
        }
        return $streak;
    }
}
