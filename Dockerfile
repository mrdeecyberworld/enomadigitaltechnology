# Runs the website exactly like shared hosting: Apache + PHP + .htaccess.
#   docker compose up        then open http://localhost:8080
FROM php:8.3-apache

RUN apt-get update \
 && apt-get install -y --no-install-recommends libjpeg62-turbo-dev libpng-dev libwebp-dev libfreetype6-dev \
 && docker-php-ext-configure gd --with-jpeg --with-webp --with-freetype \
 && docker-php-ext-install -j"$(nproc)" gd exif \
 && rm -rf /var/lib/apt/lists/* \
 && a2enmod rewrite headers expires deflate \
 && sed -ri 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf \
 && printf 'upload_max_filesize=25M\npost_max_size=30M\nmemory_limit=256M\nmax_execution_time=60\n' > /usr/local/etc/php/conf.d/enoma.ini

COPY . /var/www/html/
RUN mkdir -p /var/www/html/storage /var/www/html/assets/uploads \
 && chown -R www-data:www-data /var/www/html/storage /var/www/html/assets/uploads
