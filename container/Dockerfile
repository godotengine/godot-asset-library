FROM php:7.4-apache

RUN apt-get update && apt-get install -y git zip \
    && docker-php-ext-install mysqli pdo pdo_mysql
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer


COPY . /var/www/html/asset-library

WORKDIR /var/www/html/asset-library
RUN composer install \
    && a2enmod rewrite \
    && chown -R www-data:www-data .
USER www-data
