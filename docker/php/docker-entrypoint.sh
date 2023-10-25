#!/bin/sh
set -e

if [ ! -f config/jwt/public.pem ] || [ ! -f config/jwt/private.pem ]; then
  mkdir -p config/jwt
  openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096 -pass pass:2XG46k58GzC4DUrzA6c55xbkdATXr2
  openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout -passin pass:2XG46k58GzC4DUrzA6c55xbkdATXr2
fi

echo "Copy .env.dist.docker to .env"
cp .env.dist.docker .env

echo "Composer install"
composer install --dev

exec docker-php-entrypoint "$@"
