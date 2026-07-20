# Execution Cockpit — Laravel 12 SaaS Foundation (Phase 0)

Multi-tenant execution-tracking SaaS for Digital Builders. This folder is the **domain
foundation** you drop into a fresh Laravel 12 + Breeze install — the tenancy plumbing, the
data model, and the core toolkit models. It is not a full running app; follow the bootstrap
steps below to stand it up.

## Architecture (locked decisions)

- **Single database, `workspace_id`-scoped tenancy** with a global Eloquent scope. No
  schema/DB-per-tenant — wrong trade-off at this scale.
- **Laravel Breeze (Inertia + Vue 3 + Tailwind)** for auth. Hand-rolled `Workspace` /
  `Membership` instead of Jetstream teams.
- **`spatie/laravel-permission` in teams mode**, where `team_id = workspace_id`.
- Tenant resolution by the authenticated user's **current workspace** (session-selected),
  set in middleware — not by subdomain.

## Bootstrap

```bash
# 1. New Laravel 12 app
composer create-project laravel/laravel execution-cockpit
cd execution-cockpit

# 2. Auth starter (Inertia + Vue)
composer require laravel/breeze --dev
php artisan breeze:install vue
npm install

# 3. Core packages
composer require spatie/laravel-permission
composer require laravel-notification-channels/webpush
composer require barryvdh/laravel-dompdf maatwebsite/excel
npm install vuedraggable@next vue-apexcharts apexcharts
npm install -D vite-plugin-pwa

# 4. PostgreSQL — set DB_CONNECTION=pgsql in .env, then:
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
# In config/permission.php set 'teams' => true

# 5. Copy this folder's files over the generated app:
#    app/Models/*            → app/Models/
#    app/Models/Concerns/*   → app/Models/Concerns/
#    app/Scopes/*            → app/Scopes/
#    app/Http/Middleware/*   → app/Http/Middleware/
#    database/migrations/*   → database/migrations/

# 6. Register middleware alias 'workspace' (bootstrap/app.php) then:
php artisan migrate
php artisan webpush:vapid   # generates VAPID keys; also set VAPID_SUBJECT (mailto:) in .env
```

## What's included here

| Path | Purpose |
|---|---|
| `app/Models/Concerns/BelongsToWorkspace.php` | Trait: auto-fills `workspace_id` on create, applies the global scope |
| `app/Scopes/WorkspaceScope.php` | Global scope filtering every tenant model to the current workspace |
| `app/Support/CurrentWorkspace.php` | Request-scoped holder of the active workspace id |
| `app/Http/Middleware/SetCurrentWorkspace.php` | Resolves + sets the active workspace, syncs spatie team id |
| `app/Models/Workspace.php`, `Membership.php` | Tenancy core |
| `app/Models/DcrEntry.php`, `DcrTask.php`, `Kpi.php`, `KpiEntry.php`, `KanbanCard.php` | Representative toolkit models (rest follow the identical pattern) |
| `database/migrations/2025_01_01_000000_create_execution_cockpit_schema.php` | Full ERD in one migration |

## Remaining models (same pattern)

`WeeklyReview`, `MonthlyReview`, `FeedbackLog`, `KanbanBoard`, `KanbanColumn`,
`DisciplineChecklist`, `Streak`, `ReviewPeriod`, `AccountabilityPartnership`,
`NotificationPreference` — each is a plain Eloquent model with
`use BelongsToWorkspace;` and its `$fillable`. Tables already exist in the migration.

## Phase 1 — included in this scaffold

**Vue pages** (`resources/js/Pages/`) — Inertia + `<script setup>`, ready to wire to controllers:

| Component | Cadence | Notes |
|---|---|---|
| `Dcr.vue` | Daily | Quick entry; pending items post to Kanban; discipline checklist auto-persists |
| `WeeklyReview.vue` | Weekly | Prefilled from the week's DCRs (achieved / challenges rollup) |
| `KpiTracker.vue` | Weekly | Weekly actual entry + `vue3-apexcharts` trend with target annotation |
| `Feedback.vue` | Monthly | Add / filter / toggle status; conic-gradient closed-rate donut |
| `KanbanBoard.vue` | Ongoing | `vuedraggable` columns, stale flag, positions persisted via one reorder call |

Extra npm deps for these: `npm install vue3-apexcharts apexcharts vuedraggable@next`.

**Reminder engine** (timezone-safe, DST-safe):

- `app/Console/Commands/DispatchReminders.php` — hourly command; resolves each user's
  **local** time with Carbon, fires DCR / weekly / monthly reminders when the local hour
  matches their preference, with per-period cache idempotency. Skips the DCR nudge if today
  is already filed.
- `app/Notifications/DcrReminder.php` — WebPush primary + mail fallback, channels resolved
  per-user from `notification_preferences`.
- `routes/console.php` — schedule registration. Server cron is a single
  `* * * * * php artisan schedule:run` line; everything else is defined here.

**Routes these components expect** (add to `routes/web.php`, `->middleware(['auth','workspace'])`):

```php
Route::get   ('/dcr',            [DcrController::class,'show'])->name('dcr.show');
Route::post  ('/dcr',            [DcrController::class,'store'])->name('dcr.store');
Route::put   ('/discipline',     [DisciplineController::class,'update'])->name('discipline.update');
Route::get   ('/weekly',         [WeeklyReviewController::class,'show'])->name('weekly.show');
Route::post  ('/weekly',         [WeeklyReviewController::class,'store'])->name('weekly.store');
Route::get   ('/kpis',           [KpiController::class,'index'])->name('kpi.index');
Route::post  ('/kpis/entry',     [KpiController::class,'storeEntry'])->name('kpi.entry.store');
Route::get   ('/feedback',       [FeedbackController::class,'index'])->name('feedback.index');
Route::post  ('/feedback',       [FeedbackController::class,'store'])->name('feedback.store');
Route::put   ('/feedback/{f}/toggle', [FeedbackController::class,'toggle'])->name('feedback.toggle');
Route::delete('/feedback/{f}',   [FeedbackController::class,'destroy'])->name('feedback.destroy');
Route::get   ('/kanban',         [KanbanController::class,'index'])->name('kanban.index');
Route::post  ('/kanban/cards',   [KanbanController::class,'store'])->name('kanban.cards.store');
Route::delete('/kanban/cards/{c}',[KanbanController::class,'destroy'])->name('kanban.cards.destroy');
Route::post  ('/kanban/reorder', [KanbanController::class,'reorder'])->name('kanban.reorder');
```

Controllers are the thin remaining glue: each `index/show` returns `Inertia::render('Page', [...])`
with the props each component documents in its header comment; each `store/update` validates and
writes through the workspace-scoped models. The prototype (`../execution-cockpit.html`) is the
behavioural reference for all five.

## Controllers, auth & PWA — end-to-end wiring (included)

**Controllers** (`app/Http/Controllers/`) make the five Vue pages run: `DcrController`,
`DisciplineController`, `WeeklyReviewController`, `KpiController`, `FeedbackController`,
`KanbanController`, plus `PushSubscriptionController` and `Auth/GoogleController`. Routes are in
`routes/web.php` (module routes behind `['auth','verified','workspace']`; Google routes public).
`app/Actions/ProvisionWorkspace.php` runs first-login onboarding (workspace, owner role, seeded
KPIs by business type, Kanban board, notification prefs).

### Google login (Socialite)

```bash
composer require laravel/socialite
```

`config/services.php` — add:

```php
'google' => [
    'client_id'     => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'redirect'      => env('GOOGLE_REDIRECT_URI', '/auth/google/callback'),
],
```

`.env`:

```
GOOGLE_CLIENT_ID=xxxx.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=xxxx
GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"
```

In Google Cloud Console → Credentials → OAuth client (Web): set the authorized redirect URI to
`https://your-domain/auth/google/callback`. Drop `<GoogleLoginButton />`
(`resources/js/Components/GoogleLoginButton.vue`) into Breeze's `Login.vue` / `Register.vue`.
Run the added migration (`...add_google_auth_to_users.php`) for `google_id` / `avatar` /
nullable password.

### PWA + Web Push

```bash
npm install -D vite-plugin-pwa
composer require laravel-notification-channels/webpush
php artisan vendor:publish --provider="NotificationChannels\WebPush\WebPushServiceProvider"
php artisan migrate                 # push_subscriptions table
php artisan webpush:vapid           # writes VAPID_PUBLIC_KEY / VAPID_PRIVATE_KEY to .env
```

Set `VAPID_SUBJECT` in `.env` (a `mailto:` or https URL) — **iOS rejects pushes without it**.
Never rotate the VAPID keys (every subscription is bound to them).

Add to the app layout `<head>`:

```blade
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="vapid-key" content="{{ config('webpush.vapid.public_key') }}">
<link rel="manifest" href="/build/manifest.webmanifest">
```

- `vite.config.js` registers `vite-plugin-pwa` (autoUpdate) and imports `public/push-sw.js`
  (push + notificationclick handlers) into the generated service worker.
- `resources/js/push.js` — `enablePush()` must be called **from a user tap** (iOS rule);
  it requests permission, subscribes with the VAPID key, and POSTs to `/push/subscribe`.
- `resources/js/Components/PushToggle.vue` is a drop-in reminders switch that also shows the
  iOS "Add to Home Screen" helper when needed.
- Icons live in `public/icons/` (192, 512, 512-maskable, badge) — placeholders generated here;
  swap for your brand mark before launch.
- Run a queue worker so queued push notifications actually send:
  `php artisan queue:work` (Supervisor in production).

Test the whole loop: `php artisan reminders:dispatch --now="2026-07-20T12:30:00Z"` fires for any
user whose local hour matches and who hasn't filed today.

## Still to build (Phase 2+)

Cadence engine (`review_periods` generation + `cadence:mark-overdue` command referenced in
`routes/console.php`), PWA manifest + service worker (`vite-plugin-pwa`), monthly/quarterly
rollups, PDF/Excel export, Razorpay billing. See the uploaded implementation plan §8–10.
