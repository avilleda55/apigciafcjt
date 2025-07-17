# Usa una imagen base de PHP con Apache
FROM php:8.2-apache

# Copia todos los archivos de tu proyecto al directorio de Apache
COPY . /var/www/html/

# Habilita mod_rewrite (si usas .htaccess)
RUN a2enmod rewrite

# Configura Apache para permitir .htaccess
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Expone el puerto 10000 que Render usa
EXPOSE 10000

# Cambia el puerto por defecto de Apache a 10000
RUN sed -i 's/80/10000/g' /etc/apache2/ports.conf /etc/apache2/sites-enabled/000-default.conf

# Arranca Apache en primer plano
CMD ["apache2-foreground"]
