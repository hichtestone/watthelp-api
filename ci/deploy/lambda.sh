#!/usr/bin/env sh

echo -e "\n\n---- Generate new jwt keys ----\n"
openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096 -pass pass:2XG46k58GzC4DUrzA6c55xbkdATXr2
openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout -passin pass:2XG46k58GzC4DUrzA6c55xbkdATXr2
chmod -R 777 config/jwt

echo -e "\n\n---- Composer install ----\n"
APP_ENV=prod composer install --prefer-dist --optimize-autoloader --no-dev

echo -e "\n\n---- Warmup cache ----\n"
php bin/console cache:warmup

# --- Deploy ---
echo -e "\n\n---- DEPLOY ! ---- \n"

export AWS_ACCESS_KEY_ID=$AWS_LAMBDA_KEY
export AWS_SECRET_ACCESS_KEY=$AWS_LAMBDA_SECRET

serverless deploy --stage $APP_ENV
