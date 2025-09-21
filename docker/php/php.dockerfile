FROM php:8.2-fpm-alpine

ARG UID
ARG GID

ENV UID=${UID:-1000}
ENV GID=${GID:-1000}

WORKDIR /var/www/html

RUN addgroup -g ${GID} laravel
RUN adduser -G laravel -D -s /bin/sh -u ${UID} laravel

RUN sed -i "s/^user = .*/user = laravel/" /usr/local/etc/php-fpm.d/www.conf
RUN sed -i "s/^group = .*/group = laravel/" /usr/local/etc/php-fpm.d/www.conf

RUN apk add --no-cache \
    bash \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    postgresql-dev \
    && docker-php-ext-install pdo pdo_mysql zip bcmath gd

EXPOSE 9000
CMD ["php-fpm"]
