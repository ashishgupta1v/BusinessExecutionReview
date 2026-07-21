#!/usr/bin/env bash
#
# Execution Cockpit — server deploy for the CLONED repo (the app is already committed).
# Run on the Hostinger KVM 2 as a sudo-capable user, AFTER:
#   cd /var/www && git clone https://github.com/ashishgupta1v/BusinessExecutionReview.git
#   bash /var/www/BusinessExecutionReview/execution-cockpit/deploy/server-deploy.sh
#
# DNS first: in Hostinger hPanel add A records  @ -> VPS_IP  and  www -> VPS_IP,
# and wait until `dig +short dhandadiary.cloud` returns your IP (needed for certbot).
#
set -euo pipefail

DOMAIN="dhandadiary.cloud"
APP_DIR="/var/www/BusinessExecutionReview/execution-cockpit"
DB_NAME="execution_cockpit"
DB_USER="cockpit"
PHP="8.3"
RUN_USER="$(whoami)"

say(){ printf "\n\033[1;36m==> %s\033[0m\n" "$*"; }
pause(){ printf "\n\033[1;33m%s\033[0m\n" "$*"; read -rp "Press Enter to continue..."; }

say "Installing stack"
sudo apt update
sudo apt install -y software-properties-common curl git unzip acl
sudo add-apt-repository -y ppa:ondrej/php && sudo apt update
sudo apt install -y php${PHP}-fpm php${PHP}-cli php${PHP}-pgsql php${PHP}-mbstring php${PHP}-xml \
  php${PHP}-curl php${PHP}-zip php${PHP}-bcmath php${PHP}-gd php${PHP}-intl \
  postgresql postgresql-contrib nginx supervisor certbot python3-certbot-nginx
command -v composer >/dev/null || { curl -sS https://getcomposer.org/installer | php; sudo mv composer.phar /usr/local/bin/composer; }
command -v node >/dev/null || { curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -; sudo apt install -y nodejs; }

say "Database"
read -rsp "Choose a DB password for '$DB_USER': " DB_PASS; echo
sudo -u postgres psql -v ON_ERROR_STOP=1 <<SQL || true
CREATE DATABASE ${DB_NAME};
CREATE USER ${DB_USER} WITH ENCRYPTED PASSWORD '${DB_PASS}';
GRANT ALL PRIVILEGES ON DATABASE ${DB_NAME} TO ${DB_USER};
SQL
sudo -u postgres psql -d ${DB_NAME} -c "GRANT ALL ON SCHEMA public TO ${DB_USER};" || true

cd "$APP_DIR"

say "Installing dependencies (vendor + node_modules are not in git)"
composer install --no-dev --optimize-autoloader
npm install
npm run build

say ".env"
[ -f .env ] || cp .env.example .env
php artisan key:generate
php artisan webpush:vapid --force 2>/dev/null || php artisan webpush:vapid
sed -i "s/^DB_PASSWORD=.*/DB_PASSWORD=${DB_PASS}/" .env
sed -i "s#^APP_URL=.*#APP_URL=https://${DOMAIN}#" .env
pause "Fill GOOGLE_*, MAIL_*, VAPID_SUBJECT in ${APP_DIR}/.env and add the 'google' block to config/services.php, then continue."

say "Migrate + cache"
php artisan migrate --force
php artisan storage:link || true
php artisan config:cache && php artisan route:cache

say "Nginx"
sudo cp deploy/nginx-cockpit.conf /etc/nginx/sites-available/cockpit
sudo sed -i "s#/var/www/dhandadiary/public#${APP_DIR}/public#g" /etc/nginx/sites-available/cockpit
sudo ln -sf /etc/nginx/sites-available/cockpit /etc/nginx/sites-enabled/cockpit
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t && sudo systemctl reload nginx
sudo chown -R "$RUN_USER":www-data "$APP_DIR"
sudo chmod -R 775 storage bootstrap/cache

say "HTTPS"
if dig +short "$DOMAIN" | grep -qE '^[0-9]'; then
  sudo certbot --nginx -d "$DOMAIN" -d "www.$DOMAIN" --redirect --agree-tos -m "admin@${DOMAIN}" --non-interactive || \
    echo "certbot failed — re-run once DNS resolves: sudo certbot --nginx -d $DOMAIN -d www.$DOMAIN --redirect"
else
  echo "DNS not resolving yet — run later: sudo certbot --nginx -d $DOMAIN -d www.$DOMAIN --redirect"
fi

say "Queue worker + cron"
sudo cp deploy/cockpit-worker.conf /etc/supervisor/conf.d/cockpit-worker.conf
sudo sed -i "s#/var/www/execution-cockpit#${APP_DIR}#g; s/^user=deploy/user=${RUN_USER}/" /etc/supervisor/conf.d/cockpit-worker.conf
sudo supervisorctl reread && sudo supervisorctl update && sudo supervisorctl start cockpit-worker:* || true
( crontab -l 2>/dev/null | grep -v "artisan schedule:run"; echo "* * * * * cd ${APP_DIR} && php artisan schedule:run >> /dev/null 2>&1" ) | crontab -

say "DONE → https://${DOMAIN}"
echo "Checks: sudo supervisorctl status ; crontab -l ; php artisan reminders:dispatch --now=\"\$(date -u +%FT%TZ)\""
