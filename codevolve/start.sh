#!/usr/bin/env bash
cp ./apache/symfony.config /etc/apache2/sites-enabled
sudo service apache2 restart