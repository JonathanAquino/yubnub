# PHP 8.3 with Apache on Ubuntu 24.04
# Includes development and testing tools
FROM ubuntu:24.04

# Prevent interactive prompts during build
ENV DEBIAN_FRONTEND=noninteractive

# Install Apache, PHP 8.3, and required extensions
RUN apt-get update && apt-get install -y \
    apache2 \
    php8.3 \
    php8.3-mysql \
    php8.3-curl \
    php8.3-xml \
    php8.3-mbstring \
    php8.3-gd \
    libapache2-mod-php8.3 \
    # Development tools
    curl \
    unzip \
    git \
    && a2enmod rewrite \
    && a2enmod php8.3 \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Configure PHP for development (display errors)
RUN echo "display_errors = On" >> /etc/php/8.3/apache2/php.ini \
    && echo "error_reporting = E_ALL" >> /etc/php/8.3/apache2/php.ini

# Also configure CLI PHP for running tests
RUN echo "display_errors = On" >> /etc/php/8.3/cli/php.ini \
    && echo "error_reporting = E_ALL" >> /etc/php/8.3/cli/php.ini

# Set working directory
WORKDIR /var/www/html

# Expose port 80
EXPOSE 80

# Start Apache in foreground
CMD ["apachectl", "-D", "FOREGROUND"]
