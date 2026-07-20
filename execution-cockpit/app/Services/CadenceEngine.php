<?php

namespace App\Services;

use App\Models\ReviewPeriod;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * The review-rhythm spine. Generates day/week/month/quarter periods per user,
 * marks them complete when the matching artifact is filed, and flags past-due
 * periods as overdue (which the reminder + streak-nudge logic reads).
 *
 * All methods assume the active workspace is already set (web middleware, or
 * set explicitly per-user in the scheduled commands).
 */
class CadenceEngine
{
    /** [type => [start, end]] boundaries covering $date. */
    public function bounds(Carbon $date): array
    {
        return [
            ReviewPeriod::TYPE_DAY     => [$date->copy()->startOfDay(),   $date->copy()->endOfDay()],
            ReviewPeriod::TYPE_WEEK    => [$date->copy()->startOfWeek(),  $date->copy()->endOfWeek()],
            ReviewPeriod::TYPE_MONTH   => [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()],
            ReviewPeriod::TYPE_QUARTER => [$date->copy()->startOfQuarter(), $date->copy()->endOfQuarter()],
        ];
    }

    /** Ensure all four periods covering $date exist for the user (idempotent). */
    public function generateFor(User $user, ?Carbon $date = null): void
    {
        $date ??= now();
        foreach ($this->bounds($date) as $type => [$start, $end]) {
            ReviewPeriod::firstOrCreate(
                [
                    'user_id'      => $user->id,
                    'period_type'  => $type,
                    'period_start' => $start->toDateString(),
                ],
                [
                    'period_end' => $end->toDateString(),
                    'status'     => ReviewPeriod::OPEN,
                ]
            );
        }
    }

    /** Mark the period of $type covering $date complete, linking the artifact. */
    public function complete(User $user, string $type, Carbon $date, ?Model $reference = null): void
    {
        [$start, $end] = $this->bounds($date)[$type];

        $period = ReviewPeriod::firstOrCreate(
            ['user_id' => $user->id, 'period_type' => $type, 'period_start' => $start->toDateString()],
            ['period_end' => $end->toDateString(), 'status' => ReviewPeriod::OPEN]
        );

        $period->fill([
            'status'         => ReviewPeriod::COMPLETED,
            'completed_at'   => now(),
            'reference_type' => $reference ? $reference->getMorphClass() : $period->reference_type,
            'reference_id'   => $reference?->getKey() ?? $period->reference_id,
        ])->save();

        // A completed quarter's months, or month's weeks, aren't auto-closed here —
        // rollup completion is handled when the monthly/quarterly report is filed.
    }

    /**
     * Flag every period whose window has ended but is still open as overdue.
     * Runs cross-workspace (nightly command) — bypass the workspace scope.
     *
     * @return int number of periods flipped
     */
    public function markOverdue(?Carbon $asOf = null): int
    {
        $asOf ??= now();

        return ReviewPeriod::query()
            ->withoutGlobalScope(\App\Scopes\WorkspaceScope::class)
            ->where('status', ReviewPeriod::OPEN)
            ->whereDate('period_end', '<', $asOf->toDateString())
            ->update(['status' => ReviewPeriod::OVERDUE]);
    }
}
