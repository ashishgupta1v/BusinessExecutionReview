<?php

namespace App\Http\Controllers;

use App\Models\NotificationPreference;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class SettingsController extends Controller
{
    public function edit(Request $request)
    {
        $user = $request->user();
        $pref = $user->notificationPreference;

        return Inertia::render('Settings', [
            'timezone'  => $user->timezone,
            'workspace' => [
                'name'          => $user->currentWorkspace?->name,
                'business_type' => $user->currentWorkspace?->business_type,
            ],
            'reminders' => [
                'dcr_reminder_time'     => substr($pref?->dcr_reminder_time ?? '18:00', 0, 5),
                'weekly_reminder_dow'   => $pref?->weekly_reminder_dow ?? 5,
                'weekly_reminder_time'  => substr($pref?->weekly_reminder_time ?? '18:00', 0, 5),
                'monthly_reminder_dom'  => $pref?->monthly_reminder_dom ?? 1,
                'monthly_reminder_time' => substr($pref?->monthly_reminder_time ?? '10:00', 0, 5),
                'channels'              => $pref?->channels ?? ['webpush', 'mail'],
                'enabled'               => (bool) ($pref?->enabled ?? true),
            ],
            'timezones' => \DateTimeZone::listIdentifiers(),
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'timezone'              => ['required', Rule::in(\DateTimeZone::listIdentifiers())],
            'dcr_reminder_time'     => ['required', 'date_format:H:i'],
            'weekly_reminder_dow'   => ['required', 'integer', 'between:1,7'],
            'weekly_reminder_time'  => ['required', 'date_format:H:i'],
            'monthly_reminder_dom'  => ['required', 'integer', 'between:1,28'],
            'monthly_reminder_time' => ['required', 'date_format:H:i'],
            'channels'              => ['array'],
            'channels.*'            => ['in:webpush,mail'],
            'enabled'               => ['boolean'],
        ]);

        $user->update(['timezone' => $data['timezone']]);

        NotificationPreference::updateOrCreate(
            ['user_id' => $user->id, 'workspace_id' => $user->current_workspace_id],
            [
                'dcr_reminder_time'     => $data['dcr_reminder_time'],
                'weekly_reminder_dow'   => $data['weekly_reminder_dow'],
                'weekly_reminder_time'  => $data['weekly_reminder_time'],
                'monthly_reminder_dom'  => $data['monthly_reminder_dom'],
                'monthly_reminder_time' => $data['monthly_reminder_time'],
                'channels'              => $data['channels'] ?? ['webpush'],
                'enabled'               => $data['enabled'] ?? true,
            ]
        );

        return back()->with('status', 'Settings saved');
    }
}
