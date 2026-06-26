FROM php:8.2-apache

# Instalar dependencias del sistema y extensiones de PHP necesarias
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql zip gd \
    && a2enmod rewrite

# Configurar límites de subida y ejecución de PHP
RUN echo "upload_max_filesize = 64M\npost_max_size = 64M\nmemory_limit = 256M\nmax_execution_time = 300\nmax_input_time = 300" > /usr/local/etc/php/conf.d/uploads.ini


# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configurar directorio de trabajo
WORKDIR /var/www/html

# Copiar archivos de dependencias
COPY composer.json composer.lock ./

# Instalar dependencias con Composer
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# Copiar el resto del código del backend
COPY . .

# Terminar de configurar el autoloader de Composer
RUN composer dump-autoload --optimize

# Otorgar permisos correctos para Apache
RUN chown -R www-data:www-data /var/www/html

# Copiar y configurar el script de inicio
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Exponer el puerto 80
EXPOSE 80

# Definir el script como punto de entrada
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
