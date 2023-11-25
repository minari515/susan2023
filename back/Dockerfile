FROM php:5.6-apache

WORKDIR /var/www/html

# デッドリンクになっているライブラリのリンク先を変更する
RUN echo "deb http://archive.debian.org/debian/ stretch main" > /etc/apt/sources.list
RUN echo "deb http://archive.debian.org/debian-security stretch/updates main" >> /etc/apt/sources.list

RUN apt-get update && apt-get install -y \
    zlib1g-dev \
    libzip-dev \
    unzip \
    libcurl4-gnutls-dev

# Install required PHP extensions
RUN docker-php-ext-install curl json sockets mbstring mysqli pdo_mysql

# Enable Apache mod_rewrite
RUN a2enmod rewrite

COPY . /var/www/html
# php5.6でも動作するcomposerのバージョンを指定している
COPY --from=composer:2.2 /usr/bin/composer /usr/bin/composer