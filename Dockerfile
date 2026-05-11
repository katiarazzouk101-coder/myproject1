FROM php:8.2-apache

# تثبيت مكتبات PHP المطلوبة
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    curl \
    && docker-php-ext-install pdo pdo_mysql zip

# تثبيت Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# ضبط مجلد العمل
WORKDIR /var/www/html

# نسخ ملفات المشروع
COPY . .

# إعداد الصلاحيات
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# تثبيت الحزم باستخدام Composer
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# نسخ .env وتوليد APP_KEY
RUN cp .env.example .env \
    && php artisan key:generate

# ✅ تنظيف الكاش ثم إعادة توليده (هنا تضيف السطر يلي بدك تشوفه)
RUN php artisan config:clear \
    && php artisan config:cache

# ✅ لو عندك سكريبتات migration وبدك تنفذها تلقائيًا
RUN php artisan migrate --force

RUN php artisan storage:link 

RUN php artisan db:seed --class=CategoriesTableSeeder

# فتح البورت 80
EXPOSE 80

# بدء Laravel باستخدام السيرفر الداخلي
#CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=80"]
CMD ["apache2-foreground"]