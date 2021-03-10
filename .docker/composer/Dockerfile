FROM composer

RUN composer self-update
RUN apk add postgresql-dev
RUN docker-php-ext-configure pgsql --with-pgsql=/usr/local/pgsql
RUN docker-php-ext-install mysqli pdo pdo_mysql pdo_pgsql pgsql
