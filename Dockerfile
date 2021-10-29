FROM php:7.4-apache

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

COPY web/ /var/www/html/

COPY . /opt/wp-core-theme-components
WORKDIR /opt/wp-core-theme-components

COPY ./docker/scripts /opt/docker/scripts
RUN chmod u+x /opt/docker/scripts/setup.sh && /opt/docker/scripts/setup.sh