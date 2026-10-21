FROM php:8.2-apache

# ------------------------------------------------------------------
# إضافات النظام المطلوبة (gd لرموز QR/الباركود، zip لملفات Excel)
# ------------------------------------------------------------------
RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip \
        default-mysql-client \
        libzip-dev \
        libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
        libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql mbstring zip gd exif bcmath xml \
    && a2enmod rewrite headers env \
    && rm -rf /var/lib/apt/lists/*

# ------------------------------------------------------------------
# Composer
# ------------------------------------------------------------------
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# ------------------------------------------------------------------
# إعداد Apache و PHP
# ------------------------------------------------------------------
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/app.ini

# ------------------------------------------------------------------
# نسخ المشروع وتثبيت الاعتماديات الإنتاجية
# ------------------------------------------------------------------
COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwX storage bootstrap/cache

# ------------------------------------------------------------------
# نقطة الدخول (تطبيع نهايات الأسطر لتعمل حتى لو نُسخ المشروع من ويندوز)
# ------------------------------------------------------------------
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/entrypoint.sh \
    && chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

ENTRYPOINT ["entrypoint.sh"]
CMD ["apache2-foreground"]
