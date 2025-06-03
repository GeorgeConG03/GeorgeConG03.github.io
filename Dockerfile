FROM php:8.1-apache

# Copia todos los archivos a la imagen
COPY . /var/www/html/

# Da permisos correctos
RUN chown -R www-data:www-data /var/www/html

# Expone el puerto
EXPOSE 80
