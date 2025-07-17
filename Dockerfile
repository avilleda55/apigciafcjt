FROM php:8.2-apache

# Instala extensiones necesarias para PostgreSQL
RUN docker-php-ext-install pdo pdo_pgsql pgsql

# Copia todos los archivos
COPY . /var/www/html/

# Habilita mod_rewrite (si usas .htaccess)
RUN a2enmod rewrite

# Permitir .htaccess en Apache
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Cambia puerto a 10000 (Render)
RUN sed -i 's/80/10000/g' /etc/apache2/ports.conf /etc/apache2/sites-enabled/000-default.conf

# Exponer puerto
EXPOSE 10000

# Arrancar Apache
CMD ["apache2-foreground"]
