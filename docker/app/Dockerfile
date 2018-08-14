FROM php:7-fpm-alpine

# Install PHP extensions.
#   - Zip - used by Composer.
RUN apk add --no-cache --update zlib-dev

RUN docker-php-ext-install zip pdo pdo_mysql

# Install PHP dependency manager Composer.
RUN php -r "copy('http://getcomposer.org/installer', '/tmp/composer-setup.php');"
RUN php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer
RUN php -r "unlink('/tmp/composer-setup.php');"

ARG PM_MAX_CHILDREN=10
ARG PM_MAX_REQUESTS=500

ENV PM_MAX_CHILDREN $PM_MAX_CHILDREN
ENV PM_MAX_REQUESTS $PM_MAX_REQUESTS

# Configuration for PHP
COPY ./docker/app/php.ini /usr/local/etc/php/php.ini
COPY ./docker/app/www.conf /usr/local/etc/php-fpm.d/www.conf

# Setup application directory.
RUN mkdir -p /var/www/app
WORKDIR /var/www/app

ARG COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_ALLOW_SUPERUSER $COMPOSER_ALLOW_SUPERUSER

# Install PHP dependencies using Composer
COPY composer.json .
COPY composer.lock .

# Copy project files
COPY . .

RUN composer install --no-dev

# User www-data should have write access to storage/.
RUN chgrp -R 82 ./storage
RUN chmod -R g+w ./storage

RUN php artisan key:generate

# Create volume used by other containers
VOLUME /var/www/app
