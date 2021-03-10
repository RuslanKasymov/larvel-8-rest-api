FROM php:7.4-fpm

RUN apt update

RUN apt-get install -y \
    libsodium-dev \
    libicu-dev \
    bash \
    libpng-dev \
    libjpeg-dev \
    zlib1g-dev \
    libzip-dev \
    libpq-dev \
    exiftool

RUN docker-php-ext-configure gd --with-jpeg \
    && docker-php-ext-configure pgsql --with-pgsql=/usr/local/pgsql \
    && docker-php-ext-configure exif

RUN docker-php-ext-install \
    mysqli \
    pdo \
    pdo_mysql \
    sodium \
    gd \
    zip \
    pdo_pgsql \
    pgsql \
    exif

RUN pecl install xdebug && docker-php-ext-enable xdebug

COPY ./run.sh /tmp
ENTRYPOINT ["/tmp/run.sh","php-fpm","-F"]
