# iSpaceCoin — production

## Architecture prod

| Couche | Comment |
|--------|---------|
| HTTP | **nginx** + **PHP-FPM** (`deploy/nginx/ispace-coin.conf`) |
| App | code déployé + `public/` |
| MySQL **8.4.6** | `docker-compose.prod.yml` (bind `127.0.0.1` only) |
| Redis | idem compose (session, cache, **queue**, **Horizon**) |
| Queues | **`php artisan horizon`** via Supervisor (**pas** `queue:listen`) |
| Scheduler | `php artisan schedule:work` via Supervisor |
| BitGo (opt.) | `node bitgo-server.js` via Supervisor |
| Front | **build figé** : `npm run build` → `public/build` (pas de Vite :5173) |

## Ports (prod type)

| Port | Service | Exposition |
|------|---------|------------|
| 80 / 443 | nginx | public |
| 3306 (ou DB_PORT) | MySQL | **localhost only** |
| 6379 | Redis | **localhost only** |
| 3080 | bitgo-server | **localhost only** |
| — | Horizon UI | `https://domaine/horizon` (auth gate) |
| — | Telescope | **OFF** (`TELESCOPE_ENABLED=false`) |

## Mise en place (première fois)

```bash
git clone <repo> /var/www/ispace-coin && cd /var/www/ispace-coin

cp .env.production.example .env
# éditer .env : APP_KEY, DB_*, REDIS_*, APP_URL, secrets BitGo/Google…

php artisan key:generate

# Data
# renseigner MYSQL_ROOT_PASSWORD + REDIS_PASSWORD dans .env
docker compose -f docker-compose.prod.yml --env-file .env up -d

# App
bash scripts/prod-deploy.sh

# nginx + php-fpm (système)
# sudo cp deploy/nginx/ispace-coin.conf /etc/nginx/sites-available/ispace-coin
# sudo ln -s ... && sudo nginx -t && sudo systemctl reload nginx

# workers
# sudo cp deploy/supervisor/*.conf /etc/supervisor/conf.d/
# sudo supervisorctl reread && sudo supervisorctl update
# sudo supervisorctl start ispace-horizon ispace-scheduler
# (option) sudo supervisorctl start ispace-bitgo
```

## Déploiement suivant (update)

```bash
cd /var/www/ispace-coin
git pull
bash scripts/prod-deploy.sh
php artisan horizon:terminate   # supervisor relance Horizon
```

## Horizon UI (accès)

En prod, `/horizon` est protégé par le gate `viewHorizon`  
(`app/Providers/HorizonServiceProvider.php`).

**Aujourd’hui la liste d’emails est vide** → personne n’y accède en prod jusqu’à ce que tu ajoutes des emails :

```php
return in_array(optional($user)->email, [
    'admin@ton-domaine.example',
]);
```

## Dev vs prod

| | Dev | Prod |
|--|-----|------|
| HTTP | `php artisan serve :8002` | nginx + php-fpm |
| Front | `npm run dev` :5173 | `npm run build` |
| Queue | `queue:listen` | **Horizon** |
| MySQL | docker-compose.yml (3307) | docker-compose.prod.yml |
| Debug | `APP_DEBUG=true` | **false** |
| Telescope | souvent on | **off** |

## Fichiers livrés

- `.env.production.example`
- `docker-compose.prod.yml`
- `deploy/nginx/ispace-coin.conf`
- `deploy/supervisor/ispace-horizon.conf`
- `deploy/supervisor/ispace-scheduler.conf`
- `deploy/supervisor/ispace-bitgo.conf`
- `scripts/prod-deploy.sh`
