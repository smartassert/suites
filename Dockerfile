FROM php:8.1-fpm-buster

WORKDIR /app

ARG APP_ENV=prod
ARG DATABASE_URL=postgresql://database_user:database_password@0.0.0.0:5432/database_name?serverVersion=12&charset=utf8
ARG AUTHENTICATION_BASE_URL=https://users.example.com
ARG IS_READY=0

ENV APP_ENV=$APP_ENV
ENV DATABASE_URL=$DATABASE_URL
ENV AUTHENTICATION_BASE_URL=$AUTHENTICATION_BASE_URL
ENV IS_READY=$READY

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN apt-get -qq update && apt-get -qq -y install  \
  git \
  libpq-dev \
  libzip-dev \
  zip \
  && docker-php-ext-install \
  pdo_pgsql \
  zip \
  && apt-get autoremove -y \
  && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

COPY composer.json composer.lock /app/
COPY bin/console /app/bin/console
COPY public/index.php public/
COPY src /app/src
COPY config/bundles.php config/services.yaml /app/config/
COPY config/packages/*.yaml /app/config/packages/
COPY config/routes.yaml /app/config/
COPY migrations /app/migrations

RUN mkdir -p /app/var/log \
  && chown -R www-data:www-data /app/var/log \
  && composer check-platform-reqs --ansi \
  && echo "APP_SECRET=$(cat /dev/urandom | tr -dc 'a-zA-Z0-9' | fold -w 32 | head -n 1)" > .env \
  && composer install --no-dev --no-scripts \
  && rm composer.lock \
  && php bin/console cache:clear
