#!/usr/bin/env bash
# Vérifie que `php` CLI a les extensions requises (redis, mbstring, dom, pdo_mysql).
set -euo pipefail

need=(redis mbstring dom pdo_mysql curl)
missing=()
for m in "${need[@]}"; do
  if ! php -m 2>/dev/null | grep -qi "^${m}$"; then
    missing+=("$m")
  fi
done

if [ "${#missing[@]}" -eq 0 ]; then
  echo "✓ php $(php -r 'echo PHP_VERSION;') — extensions OK"
  exit 0
fi

echo "✗ php CLI manque : ${missing[*]}"
echo "  Sur Ubuntu, si plusieurs PHP sont installés, force 8.3 (redis inclus) :"
echo "    sudo update-alternatives --set php /usr/bin/php8.3"
echo "  puis : php -m | grep -i redis"
exit 1
