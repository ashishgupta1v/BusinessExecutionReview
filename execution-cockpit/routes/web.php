<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DcrController;
use App\Http\Controllers\DisciplineController;
use App\Http\Controllers\WeeklyReviewController;
use App\Http\Controllers\KpiController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\KanbanController;
use App\Http\Controllers\OverviewController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\PushSubscriptionController;
use App\Http\Controllers\Auth\GoogleController;

Route::get('/', function () {
    if (\Illuminate\Support\Facades\Auth::check()) {
        return redirect()->route('dcr.show');
    }
    return \Inertia\Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
});

/*
 | Google OAuth (Socialite). Public — no auth required to start the flow.
 */
Route::get('/auth/google/redirect', [GoogleController::class, 'redirect'])->name('google.redirect');
Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('google.callback');

/*
 | Authenticated + workspace-scoped app.
 | 'workspace' = App\Http\Middleware\SetCurrentWorkspace (alias registered in bootstrap/app.php).
 */
Route::middleware(['auth', 'verified', 'workspace'])->group(function () {

    // 1 · Day Close Report (Daily)
    Route::get ('/dcr', [DcrController::class, 'show'])->name('dcr.show');
    Route::post('/dcr', [DcrController::class, 'store'])->name('dcr.store');
    Route::put ('/discipline', [DisciplineController::class, 'update'])->name('discipline.update');

    // 2 · Weekly Review (Weekly)
    Route::get ('/weekly', [WeeklyReviewController::class, 'show'])->name('weekly.show');
    Route::post('/weekly', [WeeklyReviewController::class, 'store'])->name('weekly.store');

    // 3 · KPI Tracker (Weekly)
    Route::get ('/kpis', [KpiController::class, 'index'])->name('kpi.index');
    Route::post('/kpis/entry', [KpiController::class, 'storeEntry'])->name('kpi.entry.store');

    // 4 · Feedback (Monthly)
    Route::get   ('/feedback', [FeedbackController::class, 'index'])->name('feedback.index');
    Route::post  ('/feedback', [FeedbackController::class, 'store'])->name('feedback.store');
    Route::put   ('/feedback/{feedback}/toggle', [FeedbackController::class, 'toggle'])->name('feedback.toggle');
    Route::delete('/feedback/{feedback}', [FeedbackController::class, 'destroy'])->name('feedback.destroy');

    // 5 · Kanban (Ongoing)
    Route::get   ('/kanban', [KanbanController::class, 'index'])->name('kanban.index');
    Route::post  ('/kanban/cards', [KanbanController::class, 'store'])->name('kanban.cards.store');
    Route::delete('/kanban/cards/{card}', [KanbanController::class, 'destroy'])->name('kanban.cards.destroy');
    Route::post  ('/kanban/reorder', [KanbanController::class, 'reorder'])->name('kanban.reorder');

    // 6 · Overview dashboard (Live)
    Route::get('/overview', [OverviewController::class, 'index'])->name('overview.index');

    // Settings / Reminders
    Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');

    // Web Push subscription storage (called by push.js after the browser subscribes)
    Route::post  ('/push/subscribe', [PushSubscriptionController::class, 'store'])->name('push.subscribe');
    Route::delete('/push/subscribe', [PushSubscriptionController::class, 'destroy'])->name('push.unsubscribe');
});

require __DIR__ . '/auth.php'; // Breeze email/password routes
