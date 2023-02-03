FROM php:8.0-cli
RUN apt-get update
RUN apt-get install -y libzip-dev
RUN docker-php-ext-install pdo pdo_mysql zip
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
WORKDIR ./usr/src
COPY ./.env.example /usr/src/.env
COPY . .
COPY ./phpconfig.ini /usr/local/etc/php/conf.d/phpconfig.ini
RUN rm -rf /usr/src/storage/app/public *
CMD php -S 0.0.0.0:80 -t public
