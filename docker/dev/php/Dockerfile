FROM php:7.2.6-fpm-stretch

WORKDIR /var/www/dev

RUN apt-get update \
	&& apt-get install -y --no-install-recommends \
	    curl \
	    git \
		libicu-dev \
		libz-dev \
		libpq-dev \
	&& rm -rf /var/lib/apt/lists/* \
	&& docker-php-ext-install \
		intl \
		mbstring \
		pdo_mysql \
		zip \
		opcache \
    && docker-php-ext-enable opcache
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

COPY custom-php.ini /usr/local/etc/php/conf.d/custom-php.ini
COPY xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini
USER 1000
