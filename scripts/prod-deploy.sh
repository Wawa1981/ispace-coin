#!/usr/bin/env bash
# iSpaceCoin — checklist déploiement prod (à lancer sur le serveur)
# Usage : bash scripts/prod-deploy.sh
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

echo "▶ iSpaceCoin prod deploy"
echo "  root: $ROOT"
echo ""

if [ ! -f .env ]; then
  echo "✗ Pas de .env — cp .env.production.example .env puis renseigner les secrets"
  exit 1
fi

# Refuse de déployer si APP_ENV n'est pas production
APP_ENV_VAL="$(grep -E '^APP_ENV=' .env | cut -d= -f2- | tr -d '"' | tr -d "'")"
if [ "$APP_ENV_VAL" != "production" ]; then
  echo "⚠ APP_ENV=$APP_ENV_VAL (attendu: production). Continue dans 3s… Ctrl+C pour annuler"
  sleep 3
fi

echo "▶ composer install --no-dev"
composer install --no-dev --optimize-autoloader --no-interaction

echo "▶ npm ci && npm run build"
if [ -f package-lock.json ]; then
  npm ci
else
  npm install
fi
npm run build

echo "▶ Laravel optimize"
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache 2>/dev/null || true
php artisan storage:link 2>/dev/null || true

echo "▶ permissions storage/bootstrap"
chmod -R ug+rwx storage bootstrap/cache || true

echo ""
echo "✓ Build + migrate OK"
echo ""
echo "Ensuite (si pas déjà fait) :"
echo "  1) docker compose -f docker-compose.prod.yml --env-file .env up -d"
echo "  2) nginx : deploy/nginx/ispace-coin.conf"
echo "  3) supervisor : deploy/supervisor/*.conf"
echo "     sudo supervisorctl reread && sudo supervisorctl update"
echo "     sudo supervisorctl start ispace-horizon ispace-scheduler"
echo "  4) php artisan horizon:status"
echo "  5) curl -I \$APP_URL"
echo ""
echo "Dev local reste : composer dev / composer dev:all (queue:listen, pas Horizon)."
