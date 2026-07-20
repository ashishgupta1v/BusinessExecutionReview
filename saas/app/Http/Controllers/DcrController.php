<?php

namespace App\Http\Controllers;

use App\Models\DcrEntry;
use App\Models\DcrTask;
use App\Models\KanbanBoard;
use App\Models\KanbanColumn;
use App\Models\KanbanCard;
use App\Models\DisciplineChecklist;
use App\Services\CadenceEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DcrController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        $date = today()->toDateString();

        $entry = DcrEntry::with('tasks')
            ->where('user_id', $user->id)->whereDate('entry_date', $date)->first();

        $disc = DisciplineChecklist::where('user_id', $user->id)->whereDate('check_date', $date)->first();

        return Inertia::render('Dcr', [
            'date'  => $date,
            'entry' => $entry ? [
                'entry_date'      => $date,
                'moved_needle'    => $entry->moved_needle,
                'reflection_note' => $entry->reflection_note,
                'completed'  => $entry->tasks->where('type', DcrTask::TYPE_COMPLETED)->map(fn ($t) => ['title' => $t->title])->values(),
                'pending'    => $entry->tasks->where('type', DcrTask::TYPE_PENDING)->map(fn ($t) => ['title' => $t->title, 'new_deadline' => optional($t->new_deadline)->toDateString()])->values(),
                'priorities' => $entry->tasks->where('type', DcrTask::TYPE_PRIORITY)->sortBy('position')->pluck('title')->values(),
            ] : null,
            'discipline' => [
                'rule_of_5'   => $disc?->items ?? [false, false, false, false, false],
                'two_minute'  => (bool) ($disc?->two_minute_done ?? false),
                'time_blocked'=> (bool) ($disc?->time_blocked ?? false),
            ],
            'streak' => $this->streak($user->id),
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'entry_date'            => ['required', 'date'],
            'completed'             => ['array'],
            'completed.*.title'     => ['required', 'string', 'max:255'],
            'pending'               => ['array'],
            'pending.*.title'       => ['required', 'string', 'max:255'],
            'pending.*.new_deadline'=> ['nullable', 'date'],
            'priorities'            => ['array', 'max:5'],
            'priorities.*'          => ['string', 'max:255'],
            'moved_needle'          => ['boolean'],
            'reflection_note'       => ['nullable', 'string'],
        ]);

        $entry = DB::transaction(function () use ($user, $data) {
            $entry = DcrEntry::updateOrCreate(
                ['user_id' => $user->id, 'entry_date' => $data['entry_date']],
                ['moved_needle' => $data['moved_needle'] ?? false, 'reflection_note' => $data['reflection_note'] ?? null, 'submitted_at' => now()]
            );

            $entry->tasks()->delete();
            foreach ($data['completed'] ?? [] as $t) {
                $entry->tasks()->create(['type' => DcrTask::TYPE_COMPLETED, 'title' => $t['title']]);
            }
            foreach (array_values($data['priorities'] ?? []) as $i => $title) {
                if (trim($title) === '') continue;
                $entry->tasks()->create(['type' => DcrTask::TYPE_PRIORITY, 'title' => $title, 'position' => $i]);
            }
            foreach ($data['pending'] ?? [] as $t) {
                $entry->tasks()->create(['type' => DcrTask::TYPE_PENDING, 'title' => $t['title'], 'new_deadline' => $t['new_deadline'] ?? null]);
                $this->pushToBoard($t['title'], $t['new_deadline'] ?? null);
            }

            return $entry;
        });

        // Filing a DCR completes that day's period in the cadence engine.
        app(CadenceEngine::class)->complete($user, 'day', Carbon::parse($data['entry_date']), $entry);

        return back()->with('status', 'DCR filed');
    }

    /** Pending DCR tasks flow onto the board's To Do column if not already present. */
    protected function pushToBoard(string $title, ?string $due): void
    {
        $board = KanbanBoard::firstOrCreate(['name' => 'Execution Board']);
        $todo  = KanbanColumn::firstOrCreate(['board_id' => $board->id, 'name' => 'To Do'], ['position' => 0]);

        $exists = KanbanCard::where('board_id', $board->id)->where('title', $title)->exists();
        if (! $exists) {
            KanbanCard::create([
                'column_id' => $todo->id, 'board_id' => $board->id, 'title' => $title,
                'due_date' => $due, 'created_by' => request()->user()->id,
                'position' => (KanbanCard::where('column_id', $todo->id)->max('position') ?? 0) + 10,
            ]);
        }
    }

    protected function streak(int $userId): array
    {
        $dates = DcrEntry::where('user_id', $userId)->orderByDesc('entry_date')
            ->pluck('entry_date')->map(fn ($d) => Carbon::parse($d)->toDateString())->all();

        $set = array_flip($dates);
        $current = 0; $cursor = today();
        while (isset($set[$cursor->toDateString()])) { $current++; $cursor = $cursor->subDay(); }

        $longest = 0; $run = 0; $prev = null;
        foreach (array_reverse($dates) as $d) {
            $run = ($prev && Carbon::parse($prev)->addDay()->toDateString() === $d) ? $run + 1 : 1;
            $longest = max($longest, $run); $prev = $d;
        }
        return ['current' => $current, 'longest' => $longest];
    }
}
