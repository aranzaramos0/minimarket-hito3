FROM php:8.3-apache
RUN docker-php-ext-install pdo_mysql
WORKDIR /var/www/app
COPY src/ src/
COPY public/ public/
COPY apache.conf /etc/apache2/sites-available/000-default.conf
RUN printf 'display_errors=Off\nlog_errors=On\nexpose_php=Off\n' > /usr/local/etc/php/conf.d/app.ini
EXPOSE 80
