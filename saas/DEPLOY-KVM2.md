# Execution Cockpit — Deploy on Hostinger KVM 2 (no domain required)

A complete runbook: bare VPS → live HTTPS app with push, Google login, queue worker, and cron.
Target: **Hostinger KVM 2** (2 vCPU / 8 GB RAM / NVMe), **Ubuntu 24.04 LTS**.
Domain: **dhandadiary.cloud** (bought on Hostinger).

## ⚡ Fast path — one command

If you'd rather not do the 14 steps by hand, use the automated script:

```bash
# 1. In Hostinger hPanel → dhandadiary.cloud → DNS: add A records
#      @   -> YOUR_VPS_IP
#      www -> YOUR_VPS_IP
#    Wait until:  dig +short dhandadiary.cloud   returns your IP.

# 2. Upload this folder to the VPS and run the installer:
scp -r saas deploy@YOUR_VPS_IP:/tmp/saas
ssh deploy@YOUR_VPS_IP
bash /tmp/saas/deploy/setup.sh
```

`deploy/setup.sh` installs the whole stack, creates the DB, scaffolds Laravel + Breeze, overlays
these files, generates VAPID keys, migrates, builds assets, configures Nginx + Let's Encrypt,
and starts the queue worker + cron. It pauses twice — once for you to paste your Google/mail
secrets into `.env`, once for certbot (needs DNS pointed). The manual steps below are the
same actions, explained, if you prefer to run them yourself or need to debug.

> Since you own a real domain, HTTPS is a normal Hostinger DNS A-record + Let's Encrypt (Step 9),
> not DuckDNS. The DuckDNS instructions below are only a fallback if you deploy somewhere without
> a domain.

---

## 0 · What you need before starting

- Hostinger KVM 2 provisioned, root SSH access (IP + password/key from hPanel).
- A Google account (for DuckDNS login + Google OAuth credentials).
- A transactional-email account (Brevo / Postmark / Mailgun — free tiers fine).
- The `saas/` files from this repo (upload via `scp` or `git`).

Timeline: ~2–3 focused hours for a first deploy.

---

## 1 · First login & hardening

```bash
ssh root@YOUR_VPS_IP

# create a non-root sudo user
adduser deploy
usermod -aG sudo deploy

# firewall
apt update && apt install -y ufw
ufw allow OpenSSH
ufw allow 80
ufw allow 443
ufw enable

# from now on work as deploy
su - deploy
```

---

## 2 · Install the stack

```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y software-properties-common curl git unzip

# PHP 8.3 (ondrej ppa)
sudo add-apt-repository -y ppa:ondrej/php
sudo apt update
sudo apt install -y php8.3-fpm php8.3-cli php8.3-pgsql php8.3-mbstring \
  php8.3-xml php8.3-curl php8.3-zip php8.3-bcmath php8.3-gd php8.3-intl

# Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Node 20 (for building assets)
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# PostgreSQL 16
sudo apt install -y postgresql-16 postgresql-contrib      # (use distro postgresql if 16 unavailable)

# Nginx, Supervisor, Certbot
sudo apt install -y nginx supervisor certbot python3-certbot-nginx
```

---

## 3 · PostgreSQL database

```bash
sudo -u postgres psql <<'SQL'
CREATE DATABASE execution_cockpit;
CREATE USER cockpit WITH ENCRYPTED PASSWORD 'CHANGE_ME_STRONG';
GRANT ALL PRIVILEGES ON DATABASE execution_cockpit TO cockpit;
\c execution_cockpit
GRANT ALL ON SCHEMA public TO cockpit;
SQL
```

---

## 4 · Scaffold Laravel + Breeze, then drop these files in

```bash
cd /var/www
sudo mkdir execution-cockpit && sudo chown deploy:deploy execution-cockpit
cd execution-cockpit

composer create-project laravel/laravel .
composer require laravel/breeze --dev
php artisan breeze:install vue          # Inertia + Vue 3 + Tailwind

# core packages
composer require spatie/laravel-permission laravel/socialite \
  laravel-notification-channels/webpush barryvdh/laravel-dompdf maatwebsite/excel
npm install
npm install vue3-apexcharts apexcharts vuedraggable@next
npm install -D vite-plugin-pwa
```

Now copy the repo's `saas/` files over the generated app (upload the folder first with
`scp -r saas deploy@IP:/tmp/`):

```bash
cp -r /tmp/saas/app/*            app/
cp -r /tmp/saas/resources/js/*   resources/js/
cp -r /tmp/saas/database/migrations/*  database/migrations/
cp -r /tmp/saas/routes/*         routes/
cp    /tmp/saas/vite.config.js   vite.config.js
cp -r /tmp/saas/public/*         public/
```

Publish package configs and turn on spatie **teams** mode:

```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan vendor:publish --provider="NotificationChannels\WebPush\WebPushServiceProvider"
# edit config/permission.php  ->  'teams' => true,
```

Register the workspace middleware alias in `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias(['workspace' => \App\Http\Middleware\SetCurrentWorkspace::class]);
})
```

Add the Google block to `config/services.php` (see `README.md` for the snippet).

---

## 5 · Configure `.env`

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env`:

```
APP_NAME="Execution Cockpit"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://execcockpit.duckdns.org        # from Step 9

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=execution_cockpit
DB_USERNAME=cockpit
DB_PASSWORD=CHANGE_ME_STRONG

QUEUE_CONNECTION=database
SESSION_DRIVER=database

GOOGLE_CLIENT_ID=xxxx.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=xxxx
GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"

VAPID_SUBJECT="mailto:you@example.com"
# VAPID_PUBLIC_KEY / VAPID_PRIVATE_KEY filled by Step 11

MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=your_smtp_user
MAIL_PASSWORD=your_smtp_pass
MAIL_FROM_ADDRESS="you@example.com"
```

---

## 6 · Migrate & build

```bash
php artisan migrate --force
php artisan storage:link
php artisan queue:table && php artisan migrate --force   # if using database queue
npm run build
php artisan config:cache && php artisan route:cache
```

---

## 7 · Nginx + PHP-FPM

```bash
sudo tee /etc/nginx/sites-available/cockpit >/dev/null <<'NGINX'
server {
    listen 80;
    server_name execcockpit.duckdns.org;
    root /var/www/execution-cockpit/public;

    index index.php;
    charset utf-8;

    location / { try_files $uri $uri/ /index.php?$query_string; }
    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
    location ~ /\.(?!well-known).* { deny all; }
}
NGINX

sudo ln -s /etc/nginx/sites-available/cockpit /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t && sudo systemctl reload nginx

# permissions
sudo chown -R deploy:www-data /var/www/execution-cockpit
sudo chmod -R 775 storage bootstrap/cache
```

---

## 8 · (Do this before Step 9) point a name at the box

Your VPS has a public IP but no hostname. HTTPS needs one — get a **free** one:

---

## 9 · HTTPS with NO domain — DuckDNS + Let's Encrypt

1. Go to **duckdns.org**, sign in with Google.
2. Create a subdomain, e.g. `execcockpit` → you now own `execcockpit.duckdns.org`.
3. Set its **current IP** to your VPS IP (paste the IP in the box, hit "update ip").
4. Confirm DNS resolves: `dig +short execcockpit.duckdns.org` should return your IP.
5. Issue the cert (Nginx must already answer on port 80 from Step 7):

```bash
sudo certbot --nginx -d execcockpit.duckdns.org --redirect --agree-tos -m you@example.com
```

Certbot rewrites the Nginx block to listen on 443 with the cert and auto-renews via a systemd
timer. You now have valid HTTPS — **push, PWA install, and Google OAuth will work.**

> When you later buy a real domain: point its DNS A-record at the VPS, run
> `certbot --nginx -d yourdomain.com`, and change `APP_URL` + the Google redirect URI. Nothing
> in the code hard-codes a hostname.

---

## 10 · Google OAuth credentials

1. Google Cloud Console → **APIs & Services → Credentials → Create OAuth client ID → Web**.
2. Authorized redirect URI: `https://execcockpit.duckdns.org/auth/google/callback`.
3. Copy client ID + secret into `.env` (Step 5), then `php artisan config:cache`.
4. Drop `<GoogleLoginButton />` into Breeze's `resources/js/Pages/Auth/Login.vue`.

---

## 11 · VAPID keys + email

```bash
php artisan webpush:vapid        # writes VAPID_PUBLIC_KEY / VAPID_PRIVATE_KEY to .env
```

Set `VAPID_SUBJECT` in `.env` (Step 5) — **iOS rejects push without it.** Add the meta tags to
your layout `<head>` (see README): `csrf-token`, `vapid-key`, and the manifest link.
Configure the SMTP block for your email provider (Step 5). Never rotate VAPID keys after launch.

---

## 12 · Queue worker + scheduler (both required for reminders)

Supervisor keeps the queue worker alive:

```bash
sudo tee /etc/supervisor/conf.d/cockpit-worker.conf >/dev/null <<'SUP'
[program:cockpit-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/execution-cockpit/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=deploy
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/execution-cockpit/storage/logs/worker.log
stopwaitsecs=3600
SUP

sudo supervisorctl reread && sudo supervisorctl update && sudo supervisorctl start cockpit-worker:*
```

Cron runs Laravel's scheduler every minute (this is what makes `reminders:dispatch` fire hourly):

```bash
crontab -e
# add:
* * * * * cd /var/www/execution-cockpit && php artisan schedule:run >> /dev/null 2>&1
```

Test the reminder loop end-to-end:

```bash
php artisan reminders:dispatch --now="2026-07-20T12:30:00Z"
```

---

## 13 · Go-live checklist

- [ ] `APP_DEBUG=false`, `APP_ENV=production`
- [ ] `php artisan config:cache route:cache view:cache` after every `.env`/code change
- [ ] HTTPS green padlock on `https://execcockpit.duckdns.org`
- [ ] Register a test user via Google → lands on `/dcr`, workspace + KPIs seeded
- [ ] Enable reminders (desktop Chrome) → receive a test push
- [ ] Queue worker running (`sudo supervisorctl status`), cron installed (`crontab -l`)
- [ ] Nightly DB backup: `pg_dump` to a second disk / object storage, with a tested restore
- [ ] Swap placeholder icons in `public/icons/` for your real brand mark

---

## 14 · Remaining build gaps (order to tackle)

These are in the codebase's TODO surface, roughly by priority:

1. **Shared layout shell** — `resources/js/Layouts/AppLayout.vue` (in this repo). Wrap each page:
   in every Page's `<script setup>` add `import AppLayout from '@/Layouts/AppLayout.vue'` and
   `defineOptions({ layout: AppLayout })` (Inertia persistent layout). Gives all six screens the
   sidebar + bottom-nav frame from the prototype.
2. **Cadence engine** — write `app/Console/Commands/MarkOverduePeriods.php` (signature
   `cadence:mark-overdue`, already scheduled in `routes/console.php`) + a `review_periods`
   generator; wire streak calc to it.
3. **Two stubbed notifications** — `WeeklyReviewReminder`, `MonthlyDeepDiveReminder`
   (the dispatcher already calls them behind `class_exists` guards).
4. **Overview + Settings pages** as real Vue pages (the prototype's Overview dashboard + the
   `PushToggle` component are the references).
5. **Monthly deep-dive + quarterly rollups**, then **PDF/Excel export** (dompdf + maatwebsite).
6. **Tests** — Pest/PHPUnit: workspace-scope isolation, DCR store, reminder idempotency.
7. **Billing (Razorpay)** — plan gating + GST, last before public launch.

The prototype (`../execution-cockpit.html`) remains the behavioural spec for every screen.
