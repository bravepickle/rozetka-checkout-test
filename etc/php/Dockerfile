FROM php:8.3-fpm-alpine3.19

#COPY php.ini /usr/local/etc/php/php.ini

WORKDIR /app

RUN apk add --update --no-cache $PHPIZE_DEPS linux-headers \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && docker-php-ext-install -j$(nproc) opcache pdo pdo_mysql \
    && php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php -r "if (hash_file('sha384', 'composer-setup.php') === 'dac665fdc30fdd8ec78b38b9800061b4150413ff2e3b6f88543c636f7cd84f6db9189d43a81e5503cda447da73c7e5b6') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" \
    && php composer-setup.php --2 --filename=composer --install-dir=/usr/local/bin \
    && php -r "unlink('composer-setup.php');" \
    && apk del $PHPIZE_DEPS
