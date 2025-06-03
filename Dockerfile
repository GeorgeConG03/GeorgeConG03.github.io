FROM php:8.1-apache

# Instala mysqli
RUN docker-php-ext-install mysqli

# Copia tus archivos al contenedor
COPY . /var/www/html/

# Asegura permisos correctos
RUN chown -R www-data:www-data /var/www/html

# Expone el puerto del servidor
EXPOSE 80
