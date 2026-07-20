#!/usr/bin/env bash
#
# Execution Cockpit — one-shot provisioning for a fresh Hostinger KVM 2 (Ubuntu 22.04/24.04).
# Turns a bare VPS into a running app: stack, DB, Laravel scaffold + these files, HTTPS, worker, cron.
#
# USAGE (run as a sudo-capable user, NOT root):
#   1. Upload the repo's saas/ folder to the box, e.g.:  scp -r saas deploy@YOUR_IP:/tmp/saas
#   2. In Hostinger hPanel → dhandadiary.cloud DNS: add an A record  @  -> YOUR_VPS_IP
#      and an A record  www -> YOUR_VPS_IP.  Wait until `dig +short dhandadiary.cloud` returns your IP.
#   3. bash /tmp/saas/deploy/setup.sh
#
# The script is phased and re-runnable. It PAUSES twice for things only you can do:
#   - fill secrets in .env (DB pass, Google keys, mail, VAPID auto-generated)
#   - certbot (needs DNS pointed first)
#
set -euo pipefail

### ---- config (edit if needed) ----
DOMAIN="dhandadiary.cloud"
APP_DIR="/var/www/dhandadiary"
DB_NAME="execution_cockpit"
DB_USER="cockpit"
PHP="8.3"
NODE_MAJOR="20"
SAAS_SRC="$(cd "$(dirname "$0")/.." && pwd)"   # the saas/ folder this script lives in
RUN_USER="$(whoami)"

say(){ printf "\n\033[1;36m==> %s\033[0m\n" "$*"; }
pause(){ printf "\n\033[1;33m%s\033[0m\n" "$*"; read -rp "Press Enter to continue..."; }

say "Provisioning $DOMAIN  (app: $APP_DIR, src: $SAAS_SRC, user: $RUN_USER)"

### ---- 1. system packages ----
say "Installing stack (PHP $PHP, Postgres, Nginx, Node $NODE_MAJOR, Supervisor, Certbot)"
sudo apt update
sudo apt install -y software-properties-common curl git unzip acl
sudo add-apt-repository -y ppa:ondrej/php
sudo apt update
sudo apt install -y \
  php${PHP}-fpm php${PHP}-cli php${PHP}-pgsql php${PHP}-mbstring php${PHP}-xml \
  php${PHP}-curl php${PHP}-zip php${PHP}-bcmath php${PHP}-gd php${PHP}-intl \
  postgresql postgresql-contrib nginx supervisor certbot python3-certbot-nginx

if ! command -v composer >/dev/null; then
  say "Installing Composer"
  curl -sS https://getcomposer.org/installer | php
  sudo mv composer.phar /usr/local/bin/composer
fi
if ! command -v node >/dev/null || [ "$(node -v | cut -c2- | cut -d. -f1)" -lt "$NODE_MAJOR" ]; then
  say "Installing Node $NODE_MAJOR"
  curl -fsSL https://deb.nodesource.com/setup_${NODE_MAJOR}.x | sudo -E bash -
  sudo apt install -y nodejs
fi

### ---- 2. database ----
say "Creating PostgreSQL database + user"
read -rsp "Choose a DB password for user '$DB_USER': " DB_PASS; echo
sudo -u postgres psql -v ON_ERROR_STOP=1 <<SQL || true
CREATE DATABASE ${DB_NAME};
CREATE USER ${DB_USER} WITH ENCRYPTED PASSWORD '${DB_PASS}';
GRANT ALL PRIVILEGES ON DATABASE ${DB_NAME} TO ${DB_USER};
SQL
sudo -u postgres psql -d ${DB_NAME} -c "GRANT ALL ON SCHEMA public TO ${DB_USER};" || true

### ---- 3. Laravel scaffold + overlay our files ----
if [ ! -f "${APP_DIR}/artisan" ]; then
  say "Scaffolding Laravel + Breeze into $APP_DIR"
  sudo mkdir -p "$APP_DIR" && sudo chown "$RUN_USER":"$RUN_USER" "$APP_DIR"
  composer create-project laravel/laravel "$APP_DIR"
  cd "$APP_DIR"
  composer require laravel/breeze --dev
  php artisan breeze:install vue --no-interaction
else
  say "Laravel already present in $APP_DIR — skipping scaffold"
  cd "$APP_DIR"
fi

say "Installing runtime packages"
composer require spatie/laravel-permission laravel/socialite \
  laravel-notification-channels/webpush barryvdh/laravel-dompdf maatwebsite/excel
npm install
npm install vue3-apexcharts apexcharts vuedraggable@next
npm install -D vite-plugin-pwa

say "Overlaying application files from $SAAS_SRC"
cp -rv "$SAAS_SRC"/app/*                     app/
cp -rv "$SAAS_SRC"/resources/js/*            resources/js/
cp -rv "$SAAS_SRC"/database/migrations/*     database/migrations/
cp -rv "$SAAS_SRC"/routes/*                  routes/
cp -v  "$SAAS_SRC"/vite.config.js            vite.config.js
cp -rv "$SAAS_SRC"/public/*                  public/

say "Publishing package configs"
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" --force
php artisan vendor:publish --provider="NotificationChannels\WebPush\WebPushServiceProvider" --force
# turn on spatie teams mode
sed -i "s/'teams' => false/'teams' => true/" config/permission.php || true

### ---- 4. .env ----
if [ ! -f .env ]; then cp "$SAAS_SRC/.env.example" .env; fi
php artisan key:generate
php artisan webpush:vapid --force || php artisan webpush:vapid
# inject DB creds
sed -i "s/^DB_PASSWORD=.*/DB_PASSWORD=${DB_PASS}/" .env
sed -i "s#^APP_URL=.*#APP_URL=https://${DOMAIN}#" .env

cat <<NOTE

  Now open ${APP_DIR}/.env and fill:
    - GOOGLE_CLIENT_ID / GOOGLE_CLIENT_SECRET  (Google Cloud → OAuth client, Web)
        redirect URI: https://${DOMAIN}/auth/google/callback
    - MAIL_* (your Brevo/Postmark/Mailgun SMTP)
    - VAPID_SUBJECT="mailto:you@${DOMAIN}"   (keys were just generated)
  Also add the 'google' block to config/services.php  (deploy/services-google.php.snippet).
NOTE
pause "Fill .env + config/services.php, then continue to migrate & build."

### ---- 5. migrate, build, cache ----
say "Migrating + building assets"
php artisan migrate --force
php artisan storage:link || true
npm run build
php artisan config:cache && php artisan route:cache

### ---- 6. Nginx ----
say "Configuring Nginx"
sudo cp "$SAAS_SRC/deploy/nginx-cockpit.conf" /etc/nginx/sites-available/cockpit
sudo ln -sf /etc/nginx/sites-available/cockpit /etc/nginx/sites-enabled/cockpit
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t && sudo systemctl reload nginx

# permissions
sudo chown -R "$RUN_USER":www-data "$APP_DIR"
sudo chmod -R 775 storage bootstrap/cache

### ---- 7. HTTPS ----
say "Requesting Let's Encrypt certificate (DNS must already point to this box)"
if dig +short "$DOMAIN" | grep -qE '^[0-9]'; then
  sudo certbot --nginx -d "$DOMAIN" -d "www.$DOMAIN" --redirect --agree-tos -m "admin@${DOMAIN}" --non-interactive || \
    say "certbot failed — check DNS, then run: sudo certbot --nginx -d $DOMAIN -d www.$DOMAIN --redirect"
else
  say "DNS for $DOMAIN not resolving yet — skipping certbot. Run it once DNS propagates:"
  echo "   sudo certbot --nginx -d $DOMAIN -d www.$DOMAIN --redirect --agree-tos -m admin@${DOMAIN}"
fi

### ---- 8. queue worker + cron ----
say "Installing Supervisor worker + scheduler cron"
sudo cp "$SAAS_SRC/deploy/cockpit-worker.conf" /etc/supervisor/conf.d/cockpit-worker.conf
sudo sed -i "s#/var/www/execution-cockpit#${APP_DIR}#g; s/^user=deploy/user=${RUN_USER}/" /etc/supervisor/conf.d/cockpit-worker.conf
sudo supervisorctl reread && sudo supervisorctl update && sudo supervisorctl start cockpit-worker:* || true

CRON_LINE="* * * * * cd ${APP_DIR} && php artisan schedule:run >> /dev/null 2>&1"
( crontab -l 2>/dev/null | grep -v "artisan schedule:run" ; echo "$CRON_LINE" ) | crontab -

say "DONE. Visit https://${DOMAIN}"
echo "Post-checks:"
echo "  sudo supervisorctl status         # worker RUNNING"
echo "  crontab -l                        # schedule:run present"
echo "  php artisan reminders:dispatch --now=\"\$(date -u +%FT%TZ)\"   # test reminders"
