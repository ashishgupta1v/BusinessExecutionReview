<?php

namespace App\Http\Controllers;

use App\Models\FeedbackLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FeedbackController extends Controller
{
    public function index()
    {
        $items = FeedbackLog::orderByDesc('feedback_date')->orderByDesc('id')->get()
            ->map(fn (FeedbackLog $f) => [
                'id'       => $f->id,
                'date'     => $f->feedback_date->toDateString(),
                'type'     => ucfirst($f->type),
                'body'     => $f->body,
                'action'   => $f->action_taken,
                'assignee' => optional($f->assignee)->name ?? $f->assignee_name,
                'status'   => ucfirst($f->status),
            ]);

        return Inertia::render('Feedback', ['items' => $items]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type'     => ['required', 'in:Positive,Negative,Suggestion'],
            'body'     => ['required', 'string', 'max:1000'],
            'action'   => ['nullable', 'string', 'max:1000'],
            'assignee' => ['nullable', 'string', 'max:120'],
        ]);

        FeedbackLog::create([
            'created_by'    => $request->user()->id,
            'feedback_date' => today(),
            'type'          => strtolower($data['type']),
            'body'          => $data['body'],
            'action_taken'  => $data['action'] ?? null,
            'assignee_name' => $data['assignee'] ?? null,
            'status'        => 'pending',
        ]);

        return back()->with('status', 'Feedback logged');
    }

    public function toggle(FeedbackLog $feedback)
    {
        $done = $feedback->status === 'done';
        $feedback->update([
            'status'      => $done ? 'pending' : 'done',
            'resolved_at' => $done ? null : now(),
        ]);

        return back(303);
    }

    public function destroy(FeedbackLog $feedback)
    {
        $feedback->delete();
        return back(303);
    }
}
