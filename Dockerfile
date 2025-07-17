FROM php:8.2-apache

# Instala dependencias del sistema necesarias para compilar extensiones
RUN apt-get update && apt-get install -y libpq-dev && docker-php-ext-install pdo pdo_pgsql

# Copia todos los archivos del proyecto
COPY . /var/www/html/

# Habilita mod_rewrite (si usas .htaccess)
RUN a2enmod rewrite

# Configura Apache para permitir .htaccess
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Cambia el puerto de Apache a 10000 (requerido por Render)
RUN sed -i 's/80/10000/g' /etc/apache2/ports.conf /etc/apache2/sites-enabled/000-default.conf

# Exponer puerto 10000
EXPOSE 10000

# Arrancar Apache en primer plano
CMD ["apache2-foreground"]
