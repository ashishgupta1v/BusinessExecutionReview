# Execution Cockpit — Go-Live Checklist

Work top to bottom. Each item has the command/where and a ✅ done-test.
Full server setup is in `DEPLOY-KVM2.md`; this is the launch-day gate.

---

## 1 · App boots

- [ ] `.env` filled from `.env.example`, `php artisan key:generate` run
- [ ] `php artisan migrate --force` succeeds (both migrations)
- [ ] `npm run build` succeeds; `php artisan config:cache route:cache`
- [ ] `APP_DEBUG=false`, `APP_ENV=production`
- ✅ Visiting `APP_URL` shows the login page over HTTPS (green padlock)

## 2 · Google OAuth

- [ ] Google Cloud → OAuth client (Web) created
- [ ] Authorized redirect URI = `${APP_URL}/auth/google/callback` (exact match)
- [ ] `google` block added to `config/services.php` (`deploy/services-google.php.snippet`)
- [ ] `GOOGLE_CLIENT_ID` / `GOOGLE_CLIENT_SECRET` in `.env`
- [ ] `<GoogleLoginButton />` placed in `Auth/Login.vue`
- ✅ "Continue with Google" → returns to `/dcr`, a workspace + seeded KPIs exist for the user

## 3 · VAPID + email

- [ ] `php artisan webpush:vapid` run (keys written to `.env`)
- [ ] `VAPID_SUBJECT` set to a `mailto:` — **without it iOS push fails silently**
- [ ] Layout `<head>` has `csrf-token`, `vapid-key`, manifest `<link>` (see README)
- [ ] SMTP block filled; `php artisan tinker` → `Mail::raw('hi', fn($m)=>$m->to('you@x.com')->subject('t'));` arrives
- ✅ In desktop Chrome, toggle reminders on (PushToggle) → browser shows the permission prompt and subscribes

## 4 · Queue worker + scheduler (BOTH required — miss either and reminders never fire)

- [ ] Supervisor worker installed from `deploy/cockpit-worker.conf`
- [ ] `sudo supervisorctl status` shows `cockpit-worker:*  RUNNING`
- [ ] Cron line installed from `deploy/crontab.txt` (`crontab -l` shows it)
- ✅ `php artisan reminders:dispatch --now="<UTC time matching your 18:00 local>"` queues a push; the worker delivers it
- ✅ `php artisan cadence:generate` then `php artisan cadence:mark-overdue` run clean

## 5 · Security & data

- [ ] HTTPS-only; `SESSION_SECURE_COOKIE=true` in prod
- [ ] Postgres not exposed publicly (bind 127.0.0.1)
- [ ] Nightly `pg_dump` to a second location + one tested restore
- [ ] Placeholder icons in `public/icons/` swapped for the real brand mark
- [ ] Rate limiting on auth routes (Laravel default `throttle` on login)

## 6 · Smoke test the whole loop

- [ ] Sign in (Google) → file a DCR → pending task appears on Kanban
- [ ] KPI actual entry shows variance + trend
- [ ] Complete a Weekly Review → its cadence period marks complete
- [ ] Log feedback → toggle to Done
- [ ] Reminder push received and clicking it opens `/dcr`

---

## Remaining build gaps (ship order, after launch of the habit loop)

1. **Overview + Settings pages** as real Vue pages (prototype Overview + `PushToggle` are the refs).
2. **Monthly deep-dive + quarterly rollups** (aggregate weeklies + KPI snapshots).
3. **PDF / Excel export** for the coach (dompdf + maatwebsite/excel).
4. **Tests** (Pest): workspace-scope isolation, DCR store, reminder idempotency, cadence overdue.
5. **Accountability partner** (v1.1) — shareable read-only weekly snapshot.
6. **Razorpay billing** — plan gating + GST, last before charging.

Already wired and NOT on this list (done): tenancy + scope, auth (email + Google),
DCR / Weekly / KPI / Feedback / Kanban end-to-end, reminder engine, **cadence engine
(periods + overdue)**, weekly + monthly reminder notifications, PWA + push, app shell.
