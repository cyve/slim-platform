# docker build -t slim-platform .
# docker run -t -p 81:80 slim-platform
# docker tag slim-platform cyve/slim-platform && docker push cyve/slim-platform

FROM php:8.3-apache

COPY --from=composer /usr/bin/composer /usr/local/bin/composer
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions pdo_mysql opcache

COPY ./etc/apache/default.conf /etc/apache2/sites-available/000-default.conf
COPY ./ /var/www/

WORKDIR /var/www
RUN composer install --no-progress --no-interaction --optimize-autoloader --prefer-dist

ENV APP_VERSION 2.0.0
