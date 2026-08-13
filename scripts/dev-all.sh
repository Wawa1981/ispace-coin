#!/usr/bin/env bash
# Démarre EXACTEMENT la même stack sur n'importe quelle machine :
#   MySQL (:DB_PORT) + Redis (:6379) + Laravel serve + queue + logs + Vite
# Usage : composer dev:all
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

env_val() {
  local key="$1" default="${2:-}"
  local line
  line="$(grep -E "^${key}=" .env 2>/dev/null | tail -n1 || true)"
  if [ -z "$line" ]; then
    printf '%s' "$default"
    return
  fi
  printf '%s' "${line#*=}" | tr -d '"' | tr -d "'"
}

DB_PORT="$(env_val DB_PORT 3307)"
REDIS_PORT="$(env_val REDIS_PORT 6379)"
APP_PORT="${APP_PORT:-8002}"

port_open() {
  local port="$1"
  if command -v nc >/dev/null 2>&1 && nc -z 127.0.0.1 "$port" 2>/dev/null; then
    return 0
  fi
  if php -r "\$e=@fsockopen('127.0.0.1',(int)'$port',\$n,\$s,1); exit(\$e?0:1);" 2>/dev/null; then
    return 0
  fi
  if timeout 1 bash -c "echo >/dev/tcp/127.0.0.1/$port" 2>/dev/null; then
    return 0
  fi
  return 1
}

echo "▶ iSpaceCoin — démarrage identique"
echo "  projet : $ROOT"
echo "  mysql  : 127.0.0.1:${DB_PORT}"
echo "  redis  : 127.0.0.1:${REDIS_PORT}"
echo "  app    : http://127.0.0.1:${APP_PORT}"
echo ""

# Libère un port s'il est déjà pris par un vieux php/vite (sinon serve/vite crash)
free_port() {
  local port="$1"
  local pids=""
  # set -e + pipefail : les pipelines "vide" ne doivent pas tuer le script
  if command -v fuser >/dev/null 2>&1; then
    pids="$(fuser "${port}/tcp" 2>/dev/null || true)"
  fi
  if [ -z "${pids// /}" ] && command -v ss >/dev/null 2>&1; then
    pids="$(
      ss -tlnp 2>/dev/null | grep ":${port} " | sed -n 's/.*pid=\([0-9]*\).*/\1/p' | sort -u | tr '\n' ' ' || true
    )"
  fi
  # trim
  pids="$(echo "$pids" | xargs 2>/dev/null || true)"
  if [ -n "$pids" ]; then
    echo "▶ Port ${port} occupé → stop process: ${pids}"
    # shellcheck disable=SC2086
    kill $pids 2>/dev/null || true
    sleep 0.5
    # shellcheck disable=SC2086
    kill -9 $pids 2>/dev/null || true
  fi
}

free_port "$APP_PORT"
free_port 5173

if ! command -v docker >/dev/null 2>&1; then
  echo "✗ Docker introuvable."
  exit 1
fi

# --- MySQL + Redis : docker compose du projet (même partout) ---
# Si le port est déjà pris (Laradock, etc.), on réutilise le service existant.
need_compose=0
if ! port_open "$DB_PORT"; then
  need_compose=1
fi
if ! port_open "$REDIS_PORT"; then
  need_compose=1
fi

# Toujours (re)démarrer les conteneurs projet s'ils existent ; sinon compose.
docker start ispace-mysql 2>/dev/null || true
docker start ispace-redis 2>/dev/null || true

if [ "$need_compose" -eq 1 ]; then
  if [ ! -f docker-compose.yml ]; then
    echo "✗ docker-compose.yml manquant dans le projet"
    exit 1
  fi
  echo "▶ Docker Compose (mysql + redis)…"
  # Si un conteneur orphelin porte déjà le nom, on le réutilise (start), pas recreate
  if docker ps -a --format '{{.Names}}' | grep -qx ispace-mysql \
    && docker ps -a --format '{{.Names}}' | grep -qx ispace-redis; then
    docker start ispace-mysql ispace-redis >/dev/null
  else
    if docker compose version >/dev/null 2>&1; then
      docker compose --env-file .env up -d mysql redis
    else
      docker-compose --env-file .env up -d mysql redis
    fi
  fi
else
  echo "✓ MySQL déjà sur :${DB_PORT}"
  echo "✓ Redis déjà sur :${REDIS_PORT}"
fi

echo "▶ Attente MySQL :${DB_PORT}…"
for i in $(seq 1 45); do
  if port_open "$DB_PORT"; then
    echo "✓ MySQL joignable"
    break
  fi
  if [ "$i" -eq 45 ]; then
    echo "✗ Timeout MySQL 127.0.0.1:${DB_PORT}"
    exit 1
  fi
  sleep 1
done

echo "▶ Attente Redis :${REDIS_PORT}…"
for i in $(seq 1 30); do
  if port_open "$REDIS_PORT"; then
    echo "✓ Redis joignable"
    break
  fi
  if [ "$i" -eq 30 ]; then
    echo "✗ Timeout Redis 127.0.0.1:${REDIS_PORT}"
    exit 1
  fi
  sleep 1
done

sleep 1

echo ""
echo "▶ Laravel + Vite (même commande partout)…"
echo "  URL : http://127.0.0.1:${APP_PORT}"
echo ""

export COMPOSER_PROCESS_TIMEOUT=0

# php doit être 8.2+ avec redis/mbstring/dom (voir scripts/ensure-php.sh)
npx concurrently \
  -c "#93c5fd,#c4b5fd,#fb7185,#fdba74" \
  "php artisan serve --host=127.0.0.1 --port=${APP_PORT}" \
  "php artisan queue:listen --tries=1" \
  "php artisan pail --timeout=0" \
  "npm run dev" \
  --names=server,queue,logs,vite \
  --kill-others
