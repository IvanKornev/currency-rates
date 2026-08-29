FROM php:8.4-cli

ARG UID=1000
ARG GID=1000

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    curl \
    && install-php-extensions intl pdo_mysql zip opcache \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY --link --from=ghcr.io/symfony-cli/symfony-cli:v5.17 \
    /usr/local/bin/symfony /usr/local/bin/symfony

RUN groupadd -g ${GID} app && \
    useradd -u ${UID} -g app -m -s /bin/bash app && \
    mkdir -p /var/www/html && \
    chown -R app:app /var/www/html

WORKDIR /var/www/html

USER app

EXPOSE 8000
