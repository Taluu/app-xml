#!/bin/sh
set -e

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
    set -- php-fpm "$@"
fi

if [ "$1" = 'php-fpm' ] || [ "$1" = 'bin/console' ]; then
    mkdir -p var/cache var/logs var/sessions

    if [ "$FMU_APP_ENV" != 'prod' ]; then
        composer install --prefer-dist --no-progress --no-suggest --no-interaction
        bin/console assets:install

        if [ ! -e /usr/local/etc/php/conf.d/31-xdebug-remote-host.ini ]; then
            echo "xdebug.remote_host=$(/sbin/ip route|awk '/default/ { print $3 }')" >> /usr/local/etc/php/conf.d/31-xdebug-remote-host.ini
        fi
    elif [ -e /usr/local/etc/php/conf.d/30-xdebug.ini ]; then
        # no moar xdebug plz
        rm /usr/local/etc/php/conf.d/30-xdebug.ini
    fi

    # Permissions hack because setfacl does not work on Mac and Windows
    chown -R www-data var
fi

exec docker-php-entrypoint "$@"
