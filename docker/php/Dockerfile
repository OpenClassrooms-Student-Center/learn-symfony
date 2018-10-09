FROM php:fpm-alpine

# Install Symfony requirements
RUN \
    apk add --no-cache --virtual .persistent-deps \
        git \
        icu-libs \
        postgresql-libs \
        yarn \
        unzip \
        zlib-dev && \
    apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        icu-dev \
        postgresql-dev && \
    docker-php-ext-install -j$(nproc) \
        intl \
        opcache \
        pdo_pgsql \
        zip && \
    apk del .build-deps

RUN mkdir -p \
    var/data \
    var/cache \
    var/logs \
    var/sessions

# Install Composer globally
ENV COMPOSER_ALLOW_SUPERUSER 1
RUN \
    curl -sS https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer && \
    /usr/local/bin/composer self-update --preview
