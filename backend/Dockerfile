# Sử dụng image PHP 8.2 FPM chính thức
FROM php:8.2-fpm

# Cài đặt các extensions cần thiết cho PHP
# mysqli để kết nối MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Đặt thư mục làm việc
WORKDIR /var/www/html