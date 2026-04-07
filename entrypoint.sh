#!/usr/bin/env bash
set -e

echo "==> FrankenPHP dev container starting"

# Guardrails
if [ ! -d vendor ]; then
  echo "❌ vendor/ missing. Run bootstrap script first."
  echo "RUN docker compose run --rm --entrypoint \"\" web composer install --no-interaction --prefer-dist"
  exit 1
fi

if [ ! -d node_modules ]; then
  echo "❌ node_modules/ missing. Run bootstrap script first."
  echo "RUN docker compose run --rm --entrypoint \"\" web npm install"
  exit 1
fi

# Env
if [ ! -f ".env" ]; then
  cp .env.example .env
fi

if ! grep -q "^APP_KEY=base64:" .env 2>/dev/null; then
  php artisan key:generate --force
fi

# ---------- Server ----------
echo "Starting Octane (dev mode)"

exec php artisan octane:start \
  --host=0.0.0.0 \
  --port=8000 \
  --watch
