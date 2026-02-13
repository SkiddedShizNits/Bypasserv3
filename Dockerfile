FROM php:8.1-apache

COPY . /var/www/html/

RUN mkdir -p /var/www/html/data/tokens /var/www/html/data/instances && \
    chmod -R 777 /var/www/html/data && \
    a2enmod rewrite

EXPOSE 80
