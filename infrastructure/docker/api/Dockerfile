FROM php:8.4-cli-alpine

RUN set -eux; \
    apk add --no-cache libpq; \
    apk add --no-cache --virtual .build-deps $PHPIZE_DEPS postgresql-dev; \
    docker-php-ext-install pdo_pgsql; \
    apk del .build-deps; \
    php -r 'exit(extension_loaded("pdo_pgsql") ? 0 : 1);'

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . .

# Le fichier .env est volontairement exclu du contexte Docker pour ne pas
# intégrer de secrets dans l'image. Symfony Dotenv exige néanmoins qu'il existe ;
# les variables d'environnement du conteneur fournissent les valeurs réelles.
RUN touch .env

RUN composer install --no-interaction --prefer-dist --no-progress --no-scripts

EXPOSE 8000

CMD ["php", "-S", "0.0.0.0:8000", "-t", "public", "public/index.php"]
