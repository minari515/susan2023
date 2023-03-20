FROM php:5.6-apache 

WORKDIR /var/www/html

RUN apt-get update \
  && apt-get install -y \
      libonig-dev \
      libfreetype6-dev \
      libjpeg62-turbo-dev \
      libmcrypt-dev \
      libpng-dev \
      openssl libssl-dev \
      libxml2-dev \
  && docker-php-ext-install pdo_mysql mysqli mbstring \
  && docker-php-ext-install -j$(nproc) iconv mcrypt pdo_mysql mbstring xml tokenizer zip \
  && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
  && docker-php-ext-install -j$(nproc) gd \
  && docker-php-ext-install mysql \
  && pear channel-update pear.php.net \
  && pear upgrade-all \
  && pear config-set auto_discover 1 \
  && pear install DB 

# apacheのrewriteを有効にする
RUN cd /etc/apache2/mods-enabled \
    && ln -s ../mods-available/rewrite.load

COPY . /var/www/html
# php5.6でも動作するcomposerのバージョンを指定している
# COPY --from=composer:1.6.3 /usr/bin/composer /usr/bin/composer # Apple Silicon環境だと動かない？