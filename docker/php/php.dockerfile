FROM php:8.2-fpm-bookworm

ARG UID
ARG GID

ENV UID=${UID:-1000}
ENV GID=${GID:-1000}

WORKDIR /var/www/html

# Пользователь и группа, под которыми работает PHP-FPM (совпадают с владельцем файлов проекта)
RUN groupadd -g ${GID} laravel \
    && useradd -m -u ${UID} -g laravel -s /bin/bash laravel

RUN sed -i "s/^user = .*/user = laravel/" /usr/local/etc/php-fpm.d/www.conf \
    && sed -i "s/^group = .*/group = laravel/" /usr/local/etc/php-fpm.d/www.conf

# Системные пакеты и расширения PHP (GD с JPEG и FreeType, как было в Alpine-образе)
RUN apt-get update && apt-get install -y --no-install-recommends \
        bash \
        git \
        unzip \
        zip \
        libzip-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
    && docker-php-ext-configure gd --with-jpeg --with-freetype \
    && docker-php-ext-install pdo pdo_mysql zip bcmath gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# mysqldump той же версии, что и сервер базы (для php artisan db:backup).
# Берём его из образа самой базы: не нужен внешний репозиторий, а клиент MariaDB, который
# Debian ставит как «mysql-client», с MySQL 8 совместим не гарантированно.
COPY --from=mysql:8.0.38-debian /usr/bin/mysqldump /usr/bin/mysqldump
RUN mysqldump --version

EXPOSE 9000
CMD ["php-fpm"]
