<?php

use App\Actions\ProvisionWorkspace;
use App\Models\DcrEntry;
use App\Models\User;
use App\Notifications\DcrReminder;
use App\Support\CurrentWorkspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

/** 18:00 Asia/Kolkata == 12:30 UTC. */
function kolkataSixPmUtc(): string
{
    return now('UTC')->setTime(12, 30)->toIso8601String();
}

it('sends the DCR reminder once per day and is idempotent', function () {
    Notification::fake();

    $user = User::factory()->create(['timezone' => 'Asia/Kolkata']);
    app(ProvisionWorkspace::class)($user); // creates notification prefs (18:00 default)

    $this->artisan('reminders:dispatch', ['--now' => kolkataSixPmUtc()])->assertSuccessful();
    Notification::assertSentToTimes($user, DcrReminder::class, 1);

    // second run in the same period must NOT resend (cache idempotency)
    $this->artisan('reminders:dispatch', ['--now' => kolkataSixPmUtc()])->assertSuccessful();
    Notification::assertSentToTimes($user, DcrReminder::class, 1);
});

it('skips the DCR reminder when today is already filed', function () {
    Notification::fake();

    $user = User::factory()->create(['timezone' => 'Asia/Kolkata']);
    $ws = app(ProvisionWorkspace::class)($user);

    app(CurrentWorkspace::class)->set($ws->id);
    DcrEntry::create(['user_id' => $user->id, 'entry_date' => today(), 'submitted_at' => now()]);

    $this->artisan('reminders:dispatch', ['--now' => kolkataSixPmUtc()])->assertSuccessful();
    Notification::assertNothingSentTo($user);
});
