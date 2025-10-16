# Use the official PHP 8.2 image with Apache
FROM php:8.2-apache

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    libsqlite3-dev \
    && docker-php-ext-install pdo pdo_sqlite

# Set the working directory
WORKDIR /var/www/html

# Copy the application source code to the container
# We copy the 'src' directory content into the current WORKDIR
COPY / .

# Expose port 80 for the Apache web server
EXPOSE 80

