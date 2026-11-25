FROM php:8.2-apache

# Systempakete für intl (+ optional nützliches)
RUN apt-get update && apt-get install -y --no-install-recommends \
      libicu-dev locales git unzip libxslt1-dev \
      libfreetype6-dev libjpeg62-turbo-dev libpng-dev \
  && apt-get install -y --no-install-recommends --only-upgrade \
      libbrotli1 \
  && rm -rf /var/lib/apt/lists/*

# PHP-Extensions bauen/aktivieren
RUN docker-php-ext-configure intl \
  && docker-php-ext-configure gd --with-freetype --with-jpeg \
  && docker-php-ext-install -j"$(nproc)" intl opcache pdo_mysql mysqli xsl gd

# Apache-Module aktivieren
RUN a2enmod rewrite remoteip

RUN touch /var/log/vufind.log && \
    chown www-data:www-data /var/log/vufind.log && \
    chmod 644 /var/log/vufind.log

# (Optional) deutsche Locale erzeugen – hilfreich für Formatierung
RUN sed -i 's/# *de_DE.UTF-8 UTF-8/de_DE.UTF-8 UTF-8/' /etc/locale.gen && locale-gen
ENV LANG=de_DE.UTF-8 LC_ALL=de_DE.UTF-8

# Composer aus offiziellem Composer-Image übernehmen
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# Damit die Plugins aktiv sind
ENV COMPOSER_ALLOW_SUPERUSER=1

ENV VUFIND_HOME=/usr/local/vufind \
    VUFIND_LOCAL_DIR=/usr/local/vufind/local \
    VUFIND_LOCAL_MODULES=Fiddk,FiddkApi

WORKDIR /usr/local/vufind

# Nur Composer-Metadaten + build.xml zuerst kopieren für besseres Docker-Layer-Caching
COPY composer.json composer.lock ./
COPY composer.local.json build.xml ./

# Produktionsabhängigkeiten installieren (mit BuildKit-Cache für Composer-Downloads)
# --no-scripts verhindert langlaufende/hängende Phing-Hooks (z. B. installsolr) im Docker-Build.
RUN --mount=type=cache,target=/tmp/composer-cache \
    COMPOSER_CACHE_DIR=/tmp/composer-cache \
    composer install --no-dev --prefer-dist \
    --no-interaction --no-progress --no-autoloader --no-scripts

# Danach erst den restlichen Quellcode kopieren
COPY . .

# Autoloader nach dem Kopieren neu erzeugen (PSR-4 für Fiddk/FiddkApi aus composer.local.json)
# Cache leeren (Sicherheitsnetz) und prüfen, dass Fiddk im Autoloader steht
RUN rm -rf /usr/local/vufind/local/cache/*

RUN composer dump-autoload --no-dev --optimize --no-interaction -vvv

RUN grep -n 'Fiddk' vendor/composer/autoload_psr4.php

# Schreibbare Cache-Verzeichnisse für VuFind anlegen
RUN mkdir -p /usr/local/vufind/local/cache/configs \
  && chown -R www-data:www-data /usr/local/vufind/local/cache \
  && chmod -R 775 /usr/local/vufind/local/cache
