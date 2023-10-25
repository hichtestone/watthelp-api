# the different stages of this Dockerfile are meant to be built into separate images
# https://docs.docker.com/develop/develop-images/multistage-build/#stop-at-a-specific-build-stage
# https://docs.docker.com/compose/compose-file/#target


# https://docs.docker.com/engine/reference/builder/#understand-how-arg-and-from-interact
ARG PHP_VERSION=7.4
ARG NGINX_VERSION=1.19


# "php" stage
FROM php:${PHP_VERSION}-fpm-alpine

# persistent / runtime deps
RUN apk add --no-cache \
	acl \
	file \
	gettext \
	git \
	jq \
	mariadb-client \
    nodejs \
    npm \
	;

ARG APCU_VERSION=5.1.18
RUN set -eux; \
	apk add --no-cache --virtual .build-deps \
	$PHPIZE_DEPS \
	icu-dev \
	libzip-dev \
	postgresql-dev \
	zlib-dev \
	libpng-dev \
	; \
	\
	docker-php-ext-configure zip; \
	docker-php-ext-install -j$(nproc) \
	intl \
	zip \
	; \
	pecl install \
	apcu-${APCU_VERSION} \
	; \
	pecl clear-cache; \
	docker-php-ext-install \
	pdo \
	pdo_mysql \
	bcmath \
	zip \
	gd \
	sockets \
	; \
	docker-php-ext-enable \
	apcu \
	opcache \
	; \
	\
	runDeps="$( \
	scanelf --needed --nobanner --format '%n#p' --recursive /usr/local/lib/php/extensions \
	| tr ',' '\n' \
	| sort -u \
	| awk 'system("[ -e /usr/local/lib/" $1 " ]") == 0 { next } { print "so:" $1 }' \
	)"; \
	apk add --no-cache --virtual .phpexts-rundeps $runDeps; \
	\
	apk del .build-deps

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN ln -s $PHP_INI_DIR/php.ini-production $PHP_INI_DIR/php.ini
COPY php/conf.d/symfony.ini $PHP_INI_DIR/conf.d/symfony.ini

RUN if [ "$xdebug" = "true" ] ; \
then apk --update --no-cache add autoconf g++ make && \
         pecl install -f xdebug && \
         docker-php-ext-enable xdebug && \
         apk del --purge autoconf g++ make ; \
else echo "Xdebug is not installed. If you want install it add --build-arg xdebug=true"; \
fi

RUN npm install -g serverless

# https://getcomposer.org/doc/03-cli.md#composer-allow-superuser
ENV COMPOSER_ALLOW_SUPERUSER=1
