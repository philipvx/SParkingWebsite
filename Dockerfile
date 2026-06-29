FROM php:8.2-apache

# Ubah port default Apache di dalam container dari 80 ke 8080 agar cocok dengan setup lamamu
RUN sed -i 's/Listen 80/Listen 8080/g' /etc/apache2/ports.conf
RUN sed -i 's/<VirtualHost \*:80>/<VirtualHost \*:8080>/g' /etc/apache2/sites-available/000-default.conf

# Install ekstensi PHP yang umum digunakan
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copy semua source code projek SParking dari laptop ke dalam folder web server container
COPY . /var/www/html/

# Buka gerbang port 8080 di dalam container
EXPOSE 8080