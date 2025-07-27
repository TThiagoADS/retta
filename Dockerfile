# Etapa 1: Build do frontend (Vue)
FROM node:20 AS frontend

WORKDIR /app
COPY retta-front/ ./retta-front
WORKDIR /app/retta-front
RUN npm install && npm run build

# Etapa 2: Build do backend (Laravel)
FROM php:8.2-fpm AS backend

# Instala dependências do sistema
RUN apt-get update && apt-get install -y \
    git curl zip unzip libonig-dev libxml2-dev libzip-dev \
    libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-install pdo_mysql mbstring zip exif pcntl gd

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Diretório de trabalho
WORKDIR /var/www

# Copia o Laravel
COPY project-retta/ /var/www
COPY --from=frontend /app/retta-front/dist /var/www/public

# Instala dependências PHP
RUN composer install --no-dev --optimize-autoloader

# Permissões
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage

# Etapa 3: Nginx + PHP-FPM
FROM nginx:alpine

# Copia Nginx conf
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Copia frontend+backend
COPY --from=backend /var/www /var/www

# Copia PHP-FPM para rodar Laravel via socket
COPY --from=backend /usr/local/etc/php-fpm.d/ /usr/local/etc/php-fpm.d/
COPY --from=backend /usr/local/bin/php /usr/local/bin/php
COPY --from=backend /usr/local/sbin/php-fpm /usr/local/sbin/php-fpm

# Expõe porta
EXPOSE 80

# Comando para rodar PHP-FPM e Nginx juntos
CMD sh -c "php-fpm -D && nginx -g 'daemon off;'"
