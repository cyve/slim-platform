# docker build -t slim-platform .
# docker run -t -p 81:80 slim-platform
# docker tag slim-platform cyve/slim-platform && docker push cyve/slim-platform

FROM php:7.4-apache

RUN apt-get update && apt-get install -y curl zip unzip build-essential software-properties-common
RUN docker-php-ext-configure pdo_mysql --with-pdo-mysql && docker-php-ext-install pdo_mysql
RUN docker-php-ext-install opcache && docker-php-ext-enable opcache

ENV APACHE_DOCUMENT_ROOT /var/www/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

WORKDIR /var/www

COPY ./ /var/www/

COPY --from=composer /usr/bin/composer /usr/bin/composer
RUN composer install --no-progress --no-interaction --optimize-autoloader --prefer-dist

ENV DATABASE_DSN="sqlite:/data"
