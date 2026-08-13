#!/usr/bin/env bash
# Vérifie que `php` CLI a les extensions requises.
set -uo pipefail

need=(redis mbstring dom pdo_mysql curl)
missing=()
mods="$(php -m 2>/dev/null || true)"
for m in "${need[@]}"; do
  if ! printf '%s\n' "$mods" | grep -qi "^${m}$"; then
    missing+=("$m")
  fi
done

if [ "${#missing[@]}" -eq 0 ]; then
  echo "✓ php $(php -r 'echo PHP_VERSION;' 2>/dev/null) — extensions OK"
  exit 0
fi

echo "✗ php CLI manque : ${missing[*]}"
echo "  Si plusieurs PHP : sudo update-alternatives --set php /usr/bin/php8.3"
exit 1
