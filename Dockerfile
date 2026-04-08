#FROM php:8.2-apache
FROM php:8.2-cli

RUN docker-php-ext-install mysqli pdo pdo_mysql

#RUN a2enmod rewrite

WORKDIR /var/www/html

USER root

#CMD ["apache2-foreground"]
CMD ["php", "-S", "0.0.0.0:80", "-t", "/var/www/html"]