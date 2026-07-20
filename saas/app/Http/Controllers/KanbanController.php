<?php

namespace App\Http\Controllers;

use App\Models\KanbanBoard;
use App\Models\KanbanColumn;
use App\Models\KanbanCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class KanbanController extends Controller
{
    /** UI column keys ↔ stored column names. */
    protected array $names = ['todo' => 'To Do', 'doing' => 'In Progress', 'done' => 'Done'];

    public function index(Request $request)
    {
        $board = $this->board();
        $columns = ['todo' => [], 'doing' => [], 'done' => []];

        foreach ($this->names as $key => $name) {
            $col = $board->columns->firstWhere('name', $name);
            $columns[$key] = $col
                ? $col->cards()->orderBy('position')->get()->map(fn (KanbanCard $c) => [
                    'id' => $c->id, 'title' => $c->title,
                    'entered_column_at' => optional($c->entered_column_at)->toIso8601String(),
                    'due_date' => optional($c->due_date)->toDateString(),
                  ])->values()
                : [];
        }

        return Inertia::render('KanbanBoard', [
            'columns'   => $columns,
            'staleDays' => (int) ($request->user()->currentWorkspace->settings['stale_task_days'] ?? 3),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'column' => ['required', 'in:todo,doing,done'],
            'title'  => ['required', 'string', 'max:255'],
        ]);

        $board = $this->board();
        $col   = $board->columns->firstWhere('name', $this->names[$data['column']]);

        KanbanCard::create([
            'column_id' => $col->id, 'board_id' => $board->id, 'title' => $data['title'],
            'created_by' => $request->user()->id,
            'entered_column_at' => $data['column'] === 'doing' ? now() : null,
            'position' => (KanbanCard::where('column_id', $col->id)->max('position') ?? 0) + 10,
        ]);

        return back(303);
    }

    public function destroy(KanbanCard $card)
    {
        $card->delete();
        return back(303);
    }

    /** Persist new positions (and column moves) with a single upsert. */
    public function reorder(Request $request)
    {
        $data = $request->validate([
            'column'          => ['required', 'in:todo,doing,done'],
            'order'           => ['array'],
            'order.*.id'      => ['required', 'exists:kanban_cards,id'],
            'order.*.position'=> ['required', 'integer'],
        ]);

        $board = $this->board();
        $col   = $board->columns->firstWhere('name', $this->names[$data['column']]);

        DB::transaction(function () use ($data, $col) {
            foreach ($data['order'] as $row) {
                $card = KanbanCard::find($row['id']);
                if (! $card) continue;
                $movingIn = $card->column_id !== $col->id;
                $card->update([
                    'column_id' => $col->id,
                    'position'  => $row['position'],
                    // reset the stale clock only when the card actually enters "In Progress"
                    'entered_column_at' => ($movingIn && $col->name === 'In Progress') ? now() : $card->entered_column_at,
                ]);
            }
        });

        return back(303);
    }

    /** Ensure the workspace has its default board + three columns. */
    protected function board(): KanbanBoard
    {
        $board = KanbanBoard::with('columns')->first();
        if (! $board) {
            $board = KanbanBoard::create(['name' => 'Execution Board']);
            foreach (array_values($this->names) as $i => $name) {
                $board->columns()->create(['name' => $name, 'position' => $i]);
            }
            $board->load('columns');
        }
        return $board;
    }
}
