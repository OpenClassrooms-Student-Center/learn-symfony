#!/bin/bash

# Needs to be copied into "Startup Script" section of Settings.
if [ -e /var/www/html/codevolve/apache/symfony.conf ]
then
    cp /var/www/html/codevolve/apache/symfony.conf /etc/apache2/sites-enabled/
    rm /etc/apache2/sites-enabled/000-default.conf
    sudo service apache2 restart
fi

touch /root/.bashrcs/setBashDirectory.sh
printf '#!bin/bash\ncd /var/www/html' >> /root/.bashrcs/setBashDirectory.sh
