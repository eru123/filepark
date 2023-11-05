FROM php:8.1.0-apache

RUN a2enmod rewrite
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    libicu-dev \ 
    libxml2-dev

RUN docker-php-ext-install   mysqli pdo pdo_mysql zip intl opcache xml
RUN docker-php-ext-enable    mysqli pdo pdo_mysql zip intl opcache xml

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY docker_files/php.ini-development docker_files/php.ini-production /usr/local/etc/php/

COPY filepark /usr/local/bin/
RUN chmod +x /usr/local/bin/filepark

COPY . .

ENTRYPOINT ["filepark"]
