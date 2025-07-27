# Etapa 1: Build do frontend
FROM node:18 as build-frontend

WORKDIR /app

COPY . .

RUN npm install && npm run build

# Etapa 2: Laravel + Nginx ou Artisan serve
FROM php:8.2-fpm

# Instala dependências do Laravel
RUN apt-get update && apt-get install -y \
    zip unzip curl git libpng-dev libonig-dev libxml2-dev libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath zip

# Instala Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copia o Laravel
WORKDIR /var/www
COPY ./project-retta /var/www

# Copia o frontend build para dentro do Laravel
COPY --from=build-frontend /app/dist /var/www/public

# Instala dependências do Laravel
RUN composer install --no-dev --optimize-autoloader

# Dá permissão
RUN chown -R www-data:www-data /var/www && chmod -R 775 /var/www/storage

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
