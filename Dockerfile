FROM php:8.2-apache

RUN apt-get update && apt-get install -y curl \
    && docker-php-ext-install mysqli pdo pdo_mysql \
    && a2enmod rewrite

WORKDIR /var/www/html

COPY . /var/www/html/

EXPOSE 80

CMD ["apache2-foreground"]
