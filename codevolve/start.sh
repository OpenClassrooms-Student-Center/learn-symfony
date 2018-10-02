#!/usr/bin/env bash
cp /var/www/html/apache/symfony.conf /etc/apache2/sites-enabled
sudo service apache2 restart