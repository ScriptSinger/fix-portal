FROM php:8.2-fpm-alpine

# Устанавливаем системные пакеты и зависимости
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

# Создаём пользователя с UID/GID
ARG UID=1000
ARG GID=1000
RUN addgroup -g ${GID} phpuser \
    && adduser -u ${UID} -G phpuser -D -s /bin/sh -h /home/phpuser phpuser

# Настройка PHP-FPM под этого юзера
RUN sed -i "s/^user = .*/user = phpuser/" /usr/local/etc/php-fpm.d/www.conf && \
    sed -i "s/^group = .*/group = phpuser/" /usr/local/etc/php-fpm.d/www.conf && \
    sed -i "s/^listen\.owner.*/listen.owner = phpuser/" /usr/local/etc/php-fpm.d/www.conf && \
    sed -i "s/^listen\.group.*/listen.group = phpuser/" /usr/local/etc/php-fpm.d/www.conf

# Устанавливаем Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN chmod +x /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./

# Устанавливаем зависимости (можно оставить root)
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

COPY . .

# Права на storage и cache
RUN chown -R phpuser:phpuser storage bootstrap/cache

USER phpuser

EXPOSE 9000
CMD ["php-fpm"]

