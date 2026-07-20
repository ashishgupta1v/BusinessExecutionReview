<?php

use App\Actions\ProvisionWorkspace;
use App\Models\DcrEntry;
use App\Models\KanbanCard;
use App\Models\ReviewPeriod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('files a DCR: stores tasks, pushes pending to the board, completes the day period', function () {
    $user = User::factory()->create();
    app(ProvisionWorkspace::class)($user);

    $this->actingAs($user)
        ->post(route('dcr.store'), [
            'entry_date'  => today()->toDateString(),
            'completed'   => [['title' => '18 sales calls']],
            'pending'     => [['title' => 'Send Distributor Z proposal', 'new_deadline' => today()->toDateString()]],
            'priorities'  => ['Close 2 meetings', 'Launch ad'],
            'moved_needle'=> true,
            'reflection_note' => 'Good momentum.',
        ])
        ->assertRedirect();

    $entry = DcrEntry::first();
    expect($entry)->not->toBeNull()
        ->and($entry->tasks()->count())->toBe(4)              // 1 completed + 1 pending + 2 priorities
        ->and($entry->moved_needle)->toBeTrue();

    // pending task flowed onto the board
    expect(KanbanCard::where('title', 'Send Distributor Z proposal')->exists())->toBeTrue();

    // cadence: today's day period is completed
    $period = ReviewPeriod::where('period_type', 'day')->first();
    expect($period->status)->toBe(ReviewPeriod::COMPLETED);
});
