# ========================================
# DOCKERFILE - SISTEMA DE CERTIFICADOS
# ========================================

# Usar imagen base de PHP 8.1 con Apache
FROM php:8.1-apache

# Variables de entorno
ENV APACHE_DOCUMENT_ROOT=/var/www/html
ENV PHP_MEMORY_LIMIT=512M
ENV PHP_UPLOAD_MAX_FILESIZE=50M
ENV PHP_POST_MAX_SIZE=50M
ENV PHP_MAX_EXECUTION_TIME=300

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libicu-dev \
    libxml2-dev \
    libonig-dev \
    unzip \
    zip \
    nodejs \
    npm \
    && rm -rf /var/lib/apt/lists/*

# Configurar extensiones de PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    gd \
    pdo \
    pdo_mysql \
    mysqli \
    mbstring \
    intl \
    zip \
    xml \
    dom \
    curl

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configurar Apache
RUN a2enmod rewrite \
    && a2enmod ssl \
    && a2enmod headers \
    && a2enmod expires \
    && a2enmod deflate

# Crear directorio de trabajo
WORKDIR /var/www/html

# Copiar archivos de configuración
COPY docker/apache/vhost.conf /etc/apache2/sites-available/000-default.conf
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Copiar archivos de la aplicación
COPY . /var/www/html/

# Instalar dependencias de Composer
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Configurar permisos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/assets/uploads \
    && chmod -R 777 /var/www/html/admin/img/qr \
    && chmod -R 777 /var/www/html/admin/temp

# Crear directorios necesarios
RUN mkdir -p /var/www/html/assets/uploads/firmas \
    && mkdir -p /var/www/html/assets/uploads/cursos \
    && mkdir -p /var/www/html/admin/img/qr \
    && mkdir -p /var/www/html/admin/temp \
    && chmod -R 777 /var/www/html/assets/uploads \
    && chmod -R 777 /var/www/html/admin/img/qr \
    && chmod -R 777 /var/www/html/admin/temp

# Configurar variables de entorno por defecto
RUN cp env.example .env

# Exponer puerto
EXPOSE 80 443

# Script de inicio
COPY docker/scripts/entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/entrypoint.sh

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/health.php || exit 1

# Comando de inicio
ENTRYPOINT ["entrypoint.sh"]
CMD ["apache2-foreground"] 