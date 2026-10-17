FROM php:8.3-apache
WORKDIR /var/www/app
COPY src/ src/
COPY public/ public/
COPY apache.conf /etc/apache2/sites-available/000-default.conf
RUN printf 'display_errors=Off\nlog_errors=On\nexpose_php=Off\nallow_url_fopen=On\nsession.save_path=/tmp\nsession.cookie_httponly=1\nsession.cookie_samesite=Lax\n' > /usr/local/etc/php/conf.d/app.ini
EXPOSE 80
