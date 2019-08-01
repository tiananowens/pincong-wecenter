FROM php:7.2-apache 

ADD --chown=www-data:www-data . /var/www/html/

RUN cp /var/www/html/system/config/database.php.sample.php /var/www/html/system/config/database.php;\
        cp /var/www/html/system/config.inc.php.sample.php /var/www/html/system/config.inc.php;\
        mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini";\
        echo 'error_log = /var/log/apache2/error.log' >> "$PHP_INI_DIR/php.ini";\
        # echo '\nRewriteEngine On\nRewriteCond %{REQUEST_FILENAME} !-f\nRewriteCond %{REQUEST_FILENAME} !-d\nRewriteRule . /index.php [L]\n' >> /etc/apache2/apache2.conf;\
        docker-php-ext-install pdo_mysql;\
        a2enmod rewrite;\
        sed -ir -e "s/\(\$config\['debug']\s*=\s*\).*\(\s*;\)/\1true\2/g" /var/www/html/system/config/system.php;\
        sed -ir -e "s/\('host'\s*=>\s*'\).*\('\)/\1pincong-mariadb:3306\2/g" /var/www/html/system/config/database.php
