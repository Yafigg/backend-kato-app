FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libpq-dev \
    postgresql-client

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application code
COPY . /var/www/html

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Configure Apache
RUN a2enmod rewrite

# Copy Laravel public files to Apache document root
RUN cp -r public/* /var/www/html/

# Create simple health check file in document root
RUN echo '<?php echo json_encode(["status" => "healthy", "timestamp" => date("c"), "version" => "1.0.0"]); ?>' > /var/www/html/health.php

# Create simple index file
RUN echo '<?php echo "<h1>Kato App Backend is running!</h1><p>Status: Healthy</p><p>Timestamp: " . date("c") . "</p>"; ?>' > /var/www/html/index.php

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]