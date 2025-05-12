FROM node:22-alpine as builder
WORKDIR /build
COPY package.json ./
RUN npm install
COPY vue ./vue
COPY eslint.config.js \
     shims-vue.d.ts \
     tsconfig.json \
     tsconfig.node.json \
     vite.config.ts \
     ./
RUN npm run build

FROM gitlab.peppers-studio.ru:5005/peppers/webstack:8.3
WORKDIR /www
RUN chown php:nginx -R /www

# PHP-FPM
ADD docker/app/php.ini /etc/php83/php.ini
ADD docker/app/php-fpm.conf /etc/php83/php-fpm.conf

# NGINX
ADD docker/app/nginx.conf /etc/nginx/nginx.conf

# APP
USER php
COPY --chown=php:nginx ./composer.json ./.bowerrc ./
RUN composer update --no-dev
COPY --chown=php:nginx admin ./admin
COPY --chown=php:nginx api ./api
COPY --chown=php:nginx CKSource ./CKSource
COPY --chown=php:nginx common ./common
COPY --chown=php:nginx console ./console
COPY --chown=php:nginx environments ./environments
COPY --chown=php:nginx frontend ./frontend
COPY --chown=php:nginx htdocs ./htdocs
COPY --chown=php:nginx requirements ./requirements
COPY --chown=php:nginx .env.example init init-all version.php ./
COPY --from=builder --chown=php:nginx /build/vue/dist ./vue/dist
RUN php init --env="Stage Production" --overwrite=All
USER root
