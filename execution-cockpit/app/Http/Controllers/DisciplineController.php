<?php

namespace App\Http\Controllers;

use App\Models\DisciplineChecklist;
use Illuminate\Http\Request;

class DisciplineController extends Controller
{
    public function update(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'date'         => ['required', 'date'],
            'rule_of_5'    => ['array', 'size:5'],
            'rule_of_5.*'  => ['boolean'],
            'two_minute'   => ['boolean'],
            'time_blocked' => ['boolean'],
        ]);

        DisciplineChecklist::updateOrCreate(
            ['user_id' => $user->id, 'check_date' => $data['date']],
            [
                'rule_of_5_count'  => collect($data['rule_of_5'] ?? [])->filter()->count(),
                'items'            => $data['rule_of_5'] ?? [false, false, false, false, false],
                'two_minute_done'  => $data['two_minute'] ?? false,
                'time_blocked'     => $data['time_blocked'] ?? false,
            ]
        );

        return back(303);
    }
}
