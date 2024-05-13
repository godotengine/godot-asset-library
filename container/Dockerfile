FROM php:7.4-apache

RUN apt update && apt install -y git zip \
    && docker-php-ext-install mysqli pdo pdo_mysql
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer


COPY . /var/www/html/asset-library

WORKDIR /var/www/html/asset-library
RUN composer install
RUN a2enmod rewrite
RUN chown -R www-data:www-data .
USER www-data
