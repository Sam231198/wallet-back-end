#!/bin/sh
set -e

if [ ! -f .env ]; then
  cp .env.example .env
fi

if [ ! -d vendor ] || [ -z "$(ls -A vendor 2>/dev/null)" ]; then
  composer install --no-interaction --optimize-autoloader --no-dev --prefer-dist
fi

if [ ! -d node_modules ] || [ -z "$(ls -A node_modules 2>/dev/null)" ]; then
  npm install
fi

# roda migrations e gera swagger apenas no start do container
# condicione por ambiente para não rodar em produção se preferir
if [ "${APP_ENV:-local}" = "local" ]; then
  php artisan migrate --force || true
  php artisan l5-swagger:generate || true
fi

exec "$@"
