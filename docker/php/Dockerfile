FROM php:8.0.25-fpm-alpine

LABEL maintainer="Romain Gueffier <r.gueffier.pro@gmail.com>"

RUN apk add --update \
    acl \
    make \
    curl \
    nodejs \
    yarn \
    icu-dev

RUN docker-php-ext-install mysqli pdo pdo_mysql intl opcache
RUN docker-php-ext-configure intl && docker-php-ext-install intl

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    ln -s $(composer config --global home) /root/composer
ENV PATH=$PATH:/root/composer/vendor/bin COMPOSER_ALLOW_SUPERUSER=1

ADD php.ini /etc/php/conf.d/
ADD php.ini /etc/php/cli/conf.d/
ADD php-fpm.conf /etc/php/php-fpm.d/
#ADD xdebug.ini  /etc/php/conf.d/

RUN ln -snf /usr/share/zoneinfo/Europe/Paris /etc/localtime

WORKDIR /var/www/symfony

EXPOSE 9000

CMD ["php-fpm", "-F"]
