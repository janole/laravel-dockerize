FROM php:7.2-fpm

ENV DEBIAN_FRONTEND noninteractive

RUN	true \
#
# Install all necessary PHP mods
#
	&& apt-get update \
	&& apt-get install -y libxml2-dev zlib1g-dev libpq-dev libpng-dev libsodium-dev libgmp-dev \
	&& docker-php-ext-install xml pgsql pdo_pgsql zip gd gmp intl \
#
# Use the default PHP production configuration
#
	&& mv $PHP_INI_DIR/php.ini-production $PHP_INI_DIR/php.ini \
#
# Install NGINX and all other tools
#
	&& apt-get install -y nginx supervisor socat cron unzip localehelper vim procps lsof netcat-openbsd msmtp msmtp-mta \
#
# Link NGINX log to stdout
#
	&& ln -sf /dev/stdout /var/log/nginx/access.log \
	&& ln -sf /dev/stdout /var/log/nginx/error.log \
#
# Remove unnecessary NGINX files, change access rights (just to be on the safe side ...)
#
	&& rm -rf /etc/nginx/sites-available/* && chown -Rc www-data:www-data /var/www \
#
# Prepate supervisord
#
	&& mkdir -p /var/log/supervisor && rm -f /etc/supervisor/conf.d/* \
#
# Install Node.js
#
	&& curl -sL https://deb.nodesource.com/setup_11.x | bash - \
	&& apt-get install -y nodejs \
#
# Install tools for our app
#
	&& apt-get install -y imagemagick poppler-utils \
#
# Clean-up
#
	&& rm -rf /var/lib/apt/lists/*

#
# Copy our custom configs
#
COPY ./vendor/janole/laravel-dockerize/docker/configs /

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Disable warning for running composer as root
ENV COMPOSER_ALLOW_SUPERUSER=1

# Copy all files
WORKDIR /app
COPY --chown=www-data:www-data . /app

RUN true \
#
# Run composer
#
	&& composer --no-dev install \
#
# Clear cache
#
	&& php artisan cache:clear \
#
# Fix directories & permissions
#
	&& mkdir -p bootstrap/cache storage/framework/cache storage/framework/sessions storage/framework/views	

# Env File
COPY --chown=www-data:www-data .env /app/.env

#
CMD ["/usr/bin/supervisord", "-c", "/supervisord.conf"]
