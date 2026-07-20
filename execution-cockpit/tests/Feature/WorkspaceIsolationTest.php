<?php

use App\Actions\ProvisionWorkspace;
use App\Models\DcrEntry;
use App\Models\User;
use App\Support\CurrentWorkspace;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/*
 | NOTE: the schema uses jsonb, so tests run against a PostgreSQL test database.
 | In phpunit.xml set DB_CONNECTION=pgsql and DB_DATABASE=execution_cockpit_test.
 */

it('scopes tenant data to the active workspace', function () {
    $provision = app(ProvisionWorkspace::class);

    $alice = User::factory()->create();
    $bob   = User::factory()->create();
    $wsA = $provision($alice);
    $wsB = $provision($bob);

    // Alice files a DCR in her workspace.
    app(CurrentWorkspace::class)->set($wsA->id);
    DcrEntry::create(['user_id' => $alice->id, 'entry_date' => today(), 'submitted_at' => now()]);

    expect(DcrEntry::count())->toBe(1);

    // Switching to Bob's workspace, the global scope hides Alice's row.
    app(CurrentWorkspace::class)->set($wsB->id);
    expect(DcrEntry::count())->toBe(0);

    // The escape hatch sees across tenants.
    expect(DcrEntry::withoutWorkspaceScope()->count())->toBe(1);
});
