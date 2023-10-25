mkdir -p config/jwt
openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096 -pass pass:2XG46k58GzC4DUrzA6c55xbkdATXr2
openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout -passin pass:2XG46k58GzC4DUrzA6c55xbkdATXr2
chmod -R 777 config/jwt

cp .env.dist.docker .env

composer install --optimize-autoloader --dev
