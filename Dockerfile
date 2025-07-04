FROM php:8.0-apache

RUN docker-php-ext-install mysqli


COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html
