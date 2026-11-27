FROM php:8.4-apache

WORKDIR /var/www/html

COPY . .

ENV API_URL=http://127.0.0.1/api/

EXPOSE 80
