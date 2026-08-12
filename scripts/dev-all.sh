#!/usr/bin/env bash
# Démarre MySQL (Laradock) + stack Laravel/Vite (front + back)
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

# Chemin Laradock (override: export LARADOCK_PATH=/chemin/vers/laradock)
LARADOCK_PATH="${LARADOCK_PATH:-$HOME/laradock}"
DB_PORT="${DB_PORT:-$(grep -E '^DB_PORT=' .env 2>/dev/null | cut -d= -f2- | tr -d '"' || echo 3307)}"
APP_PORT="${APP_PORT:-8002}"

echo "▶ iSpaceCoin — démarrage complet"
echo "  projet : $ROOT"
echo "  mysql  : 127.0.0.1:${DB_PORT}"
echo "  app    : http://127.0.0.1:${APP_PORT}"
echo ""

# --- MySQL Docker ---
if ! command -v docker >/dev/null 2>&1; then
  echo "✗ Docker introuvable. Installe Docker ou démarre MySQL manuellement."
  exit 1
fi

if [ -f "$LARADOCK_PATH/docker-compose.yml" ] || [ -f "$LARADOCK_PATH/compose.yaml" ]; then
  echo "▶ Laradock MySQL ($LARADOCK_PATH)…"
  (
    cd "$LARADOCK_PATH"
    if docker compose version >/dev/null 2>&1; then
      docker compose up -d mysql
    else
      docker-compose up -d mysql
    fi
  )
elif docker ps -a --format '{{.Names}}' | grep -qx 'laradock-mysql-1'; then
  echo "▶ Conteneur laradock-mysql-1…"
  docker start laradock-mysql-1 >/dev/null
else
  echo "⚠ Laradock non trouvé ($LARADOCK_PATH) et pas de conteneur laradock-mysql-1."
  echo "  Démarre MySQL à la main, puis relance."
fi

# Attendre que le port MySQL réponde
echo "▶ Attente MySQL sur le port ${DB_PORT}…"
ready=0
for i in $(seq 1 45); do
  if command -v nc >/dev/null 2>&1 && nc -z 127.0.0.1 "$DB_PORT" 2>/dev/null; then
    ready=1
  elif php -r "\$e=@fsockopen('127.0.0.1',(int)'$DB_PORT',\$n,\$s,1); exit(\$e?0:1);" 2>/dev/null; then
    ready=1
  elif timeout 1 bash -c "echo >/dev/tcp/127.0.0.1/$DB_PORT" 2>/dev/null; then
    ready=1
  fi
  if [ "$ready" -eq 1 ]; then
    echo "✓ MySQL joignable"
    break
  fi
  if [ "$i" -eq 45 ]; then
    echo "✗ Timeout : MySQL ne répond pas sur 127.0.0.1:${DB_PORT}"
    echo "  Vérifie: docker ps | grep mysql"
    exit 1
  fi
  sleep 1
done

# Petit délai pour que mysqld accepte les connexions après le port ouvert
sleep 2

echo ""
echo "▶ Laravel + Vite (front + back ensemble)…"
echo "  URL : http://127.0.0.1:${APP_PORT}"
echo ""

export COMPOSER_PROCESS_TIMEOUT=0

# serve sur le même port que APP_URL (.env → 8002)
npx concurrently \
  -c "#93c5fd,#c4b5fd,#fb7185,#fdba74" \
  "php artisan serve --host=127.0.0.1 --port=${APP_PORT}" \
  "php artisan queue:listen --tries=1" \
  "php artisan pail --timeout=0" \
  "npm run dev" \
  --names=server,queue,logs,vite \
  --kill-others
