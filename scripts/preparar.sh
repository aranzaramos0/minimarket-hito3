#!/bin/sh
set -eu
cd "$(dirname "$0")/.."
if [ -f .env ]; then
  echo 'Ya existe .env; se conserva su configuracion.'
  exit 0
fi
ROOT_PASS=$(od -An -N24 -tx1 /dev/urandom | tr -d ' \n')
APP_PASS=$(od -An -N24 -tx1 /dev/urandom | tr -d ' \n')
sed -e "s/cambiar_clave_root_local/$ROOT_PASS/" -e "s/cambiar_clave_app_local/$APP_PASS/" .env.example > .env
chmod 600 .env
echo '.env creado. Ejecuta: docker compose up -d --build'
