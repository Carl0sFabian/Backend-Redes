#!/bin/bash

# Crear la carpeta config si no existe
mkdir -p /var/www/html/config

# Copiar archivos secretos desde /etc/secrets si están disponibles
if [ -f /etc/secrets/.env ]; then
    cp /etc/secrets/.env /var/www/html/.env
fi

if [ -f /etc/secrets/ca.pem ]; then
    cp /etc/secrets/ca.pem /var/www/html/config/ca.pem
fi

if [ -f /etc/secrets/client_secret.json ]; then
    cp /etc/secrets/client_secret.json /var/www/html/config/client_secret.json
fi

if [ -f /etc/secrets/token.json ]; then
    cp /etc/secrets/token.json /var/www/html/config/token.json
fi

# Cambiar propiedad de todos los archivos al usuario de Apache (www-data)
chown -R www-data:www-data /var/www/html

# Iniciar Apache en primer plano
exec apache2-foreground
