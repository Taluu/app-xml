FROM php:7.3-fpm-alpine
MAINTAINER Baptiste Clavié <clavie.b@gmail.com>

RUN apk add --update --no-cache --virtual .persistent-deps \
        git \
        icu-libs \
        zlib \
        libuuid \
        postgresql-dev

ENV APCU_VERSION 5.1.17

RUN set -xe \
    && apk add --update --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        icu-dev \
        zlib-dev \
        util-linux-dev \
    && docker-php-ext-install \
        intl \
        pgsql \
        pdo_pgsql \
        zip \
        bcmath \
        sockets \
    && pecl install \
        apcu-${APCU_VERSION} \
        xdebug \
        uuid \
    && docker-php-ext-enable --ini-name 05-opcache.ini opcache \
    && docker-php-ext-enable --ini-name 20-apcu.ini apcu \
    && docker-php-ext-enable --ini-name 20-intl.ini intl \
    && docker-php-ext-enable --ini-name 30-xdebug.ini xdebug \
    && apk del .build-deps

# Add source code
WORKDIR /srv/api

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
COPY .docker/php/php.ini /usr/local/etc/php/conf.d/docker-vars.ini

RUN php -r "copy('https://github.com/phpstan/phpstan/releases/download/0.11.5/phpstan.phar', '/usr/local/bin/phpstan');"
RUN chmod +x /usr/local/bin/phpstan

# https://getcomposer.org/doc/03-cli.md#composer-allow-superuser
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer global require "hirak/prestissimo:^0.3" --prefer-dist --no-progress --no-suggest --classmap-authoritative \
    && composer clear-cache
ENV PATH="${PATH}:/root/.composer/vendor/bin"

ARG APP_ENV=dev

# Install backend vendors
COPY composer.json composer.lock ./
RUN composer install --prefer-dist --no-dev --no-progress --no-suggest \
    && composer clear-cache

COPY . ./

RUN mkdir -p var/cache var/logs \
    && composer dump-autoload --classmap-authoritative --no-dev \
    && chmod +x bin/console && sync \
    && chown -R www-data var

VOLUME /secrets

COPY .docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

RUN rm -rf .docker
RUN rm -rf var/jwt var/cloud

# tmp configuration ?!
# alpine wtf ?!
RUN chmod -R 1777 /tmp

ENTRYPOINT ["docker-entrypoint"]
CMD ["php-fpm"]
