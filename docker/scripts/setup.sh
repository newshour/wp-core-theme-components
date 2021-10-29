# Setup apache configs and mods
{ \
    echo 'ServerName localhost'; \
} > /etc/apache2/conf-available/localhost.conf

a2enconf localhost
a2enmod deflate expires ext_filter filter headers mime rewrite setenvif