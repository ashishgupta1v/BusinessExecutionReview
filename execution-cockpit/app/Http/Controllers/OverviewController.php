<?php

namespace App\Http\Controllers;

use App\Models\DcrEntry;
use App\Models\DisciplineChecklist;
use App\Models\FeedbackLog;
use App\Models\Kpi;
use App\Models\WeeklyReview;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class OverviewController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $today = today();

        $dcrDates = DcrEntry::where('user_id', $user->id)
            ->pluck('entry_date')->map(fn ($d) => Carbon::parse($d)->toDateString())->flip();

        // streak
        $current = 0; $c = $today->copy();
        while ($dcrDates->has($c->toDateString())) { $current++; $c->subDay(); }
        $sorted = $dcrDates->keys()->sort()->values();
        $longest = 0; $run = 0; $prev = null;
        foreach ($sorted as $d) {
            $run = ($prev && Carbon::parse($prev)->addDay()->toDateString() === $d) ? $run + 1 : 1;
            $longest = max($longest, $run); $prev = $d;
        }

        // discipline levels keyed by date
        $disc = DisciplineChecklist::where('user_id', $user->id)->get()->keyBy(fn ($x) => $x->check_date->toDateString());

        // 18-week heatmap
        $weeks = 18; $start = $today->copy()->subWeeks($weeks);
        $cells = [];
        for ($i = 0; $i < $weeks * 7; $i++) {
            $iso = $start->copy()->addDays($i)->toDateString();
            $has = $dcrDates->has($iso);
            $d = $disc->get($iso);
            $dl = $d ? ($d->rule_of_5_count + ($d->two_minute_done ? 1 : 0) + ($d->time_blocked ? 1 : 0)) : 0;
            $cells[] = ['date' => $iso, 'level' => $has ? ($dl >= 5 ? 4 : ($dl >= 3 ? 3 : 2)) : ($dl > 0 ? 1 : 0)];
        }

        // KPI status — latest actual vs target
        $kpiStatus = Kpi::where('active', true)->orderBy('sort_order')->get()->map(function (Kpi $k) {
            $last = $k->entries()->orderByDesc('iso_year')->orderByDesc('iso_week')->first();
            $actual = $last ? (float) $last->actual : 0;
            $good = $k->direction === 'higher_better' ? $actual >= $k->target_default : $actual <= $k->target_default;
            return ['name' => $k->name, 'unit' => $k->unit, 'actual' => $actual, 'target' => (float) $k->target_default, 'good' => $good];
        });

        $fbTotal = FeedbackLog::count();
        $fbOpen  = FeedbackLog::where('status', 'pending')->count();

        // rule-of-5 average this week
        $weekDisc = $disc->filter(fn ($x) => $x->check_date->isoWeek === $today->isoWeek && $x->check_date->isoWeekYear === $today->isoWeekYear);
        $r5avg = $weekDisc->count() ? round($weekDisc->avg('rule_of_5_count')) : 0;

        return Inertia::render('Overview', [
            'streak'        => ['current' => $current, 'longest' => $longest],
            'dcrThisMonth'  => $dcrDates->keys()->filter(fn ($d) => str_starts_with($d, $today->format('Y-m')))->count(),
            'openFeedback'  => $fbOpen,
            'reviewsDone'   => WeeklyReview::where('user_id', $user->id)->where('status', 'complete')->count(),
            'filled30'      => collect(range(0, 29))->filter(fn ($i) => $dcrDates->has($today->copy()->subDays($i)->toDateString()))->count(),
            'rule5Avg'      => $r5avg,
            'kpiStatus'     => $kpiStatus,
            'kpiOnTarget'   => $kpiStatus->where('good', true)->count(),
            'feedback'      => ['total' => $fbTotal, 'open' => $fbOpen, 'done' => $fbTotal - $fbOpen],
            'heatmap'       => $cells,
        ]);
    }
}
