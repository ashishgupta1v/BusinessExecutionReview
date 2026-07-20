<?php

namespace App\Http\Controllers;

use App\Models\DcrEntry;
use App\Models\DcrTask;
use App\Models\WeeklyReview;
use App\Services\CadenceEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class WeeklyReviewController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        $now  = now();
        $isoYear = (int) $now->isoWeekYear;
        $isoWeek = (int) $now->isoWeek;
        $weekKey = sprintf('%d-W%02d', $isoYear, $isoWeek);

        [$start, $end] = [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()];

        $entries = DcrEntry::with('tasks')
            ->where('user_id', $user->id)
            ->whereBetween('entry_date', [$start->toDateString(), $end->toDateString()])
            ->get();

        $achieved = $entries->flatMap(fn ($e) => $e->tasks->where('type', DcrTask::TYPE_COMPLETED)->pluck('title'))->values();
        $challenges = $entries->flatMap(fn ($e) => $e->tasks->where('type', DcrTask::TYPE_PENDING)->pluck('title'))->values();

        $review = WeeklyReview::where('user_id', $user->id)
            ->where('iso_year', $isoYear)->where('iso_week', $isoWeek)->first();

        return Inertia::render('WeeklyReview', [
            'week'   => $weekKey,
            'review' => $review ? [
                'status'              => $review->status,
                'planned_goals'       => $this->join($review->planned_goals),
                'achieved'            => $this->join($review->achieved),
                'major_wins'          => $review->major_wins,
                'challenges'          => $review->challenges,
                'moved_needle_answer' => $review->moved_needle_answer,
                'next_week_focus'     => $this->join($review->next_week_focus),
            ] : null,
            'prefill' => [
                'achieved'     => $achieved,
                'challenges'   => $challenges,
                'dcrCount'     => $entries->count(),
                'doneCount'    => $achieved->count(),
                'carriedCount' => $challenges->count(),
                'needleDays'   => $entries->where('moved_needle', true)->count(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'iso_week'            => ['required', 'string'],   // "2026-W30"
            'planned_goals'       => ['nullable', 'string'],
            'achieved'            => ['nullable', 'string'],
            'major_wins'          => ['nullable', 'string'],
            'challenges'          => ['nullable', 'string'],
            'moved_needle_answer' => ['nullable', 'string'],
            'next_week_focus'     => ['nullable', 'string'],
            'status'              => ['required', 'in:draft,complete'],
        ]);

        [$y, $w] = array_map('intval', explode('-W', $data['iso_week']));

        $review = WeeklyReview::updateOrCreate(
            ['user_id' => $user->id, 'iso_year' => $y, 'iso_week' => $w],
            [
                'planned_goals'       => $this->lines($data['planned_goals'] ?? ''),
                'achieved'            => $this->lines($data['achieved'] ?? ''),
                'major_wins'          => $data['major_wins'] ?? null,
                'challenges'          => $data['challenges'] ?? null,
                'moved_needle_answer' => $data['moved_needle_answer'] ?? null,
                'next_week_focus'     => array_slice($this->lines($data['next_week_focus'] ?? ''), 0, 3),
                'status'              => $data['status'],
                'completed_at'        => $data['status'] === 'complete' ? now() : null,
            ]
        );

        // Completing the review closes that week's cadence period.
        if ($data['status'] === 'complete') {
            app(CadenceEngine::class)->complete($user, 'week', Carbon::now()->setISODate($y, $w, 1), $review);
        }

        return back()->with('status', 'Weekly review saved');
    }

    private function lines(?string $text): array
    {
        return collect(preg_split('/\r?\n/', (string) $text))->map(fn ($l) => trim($l))->filter()->values()->all();
    }
    private function join($arr): string
    {
        return is_array($arr) ? implode("\n", $arr) : (string) $arr;
    }
}
