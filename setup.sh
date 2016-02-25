#!/bin/bash

apt-get install -y apache2 php5 network-manager fbi unclutter matchbox xorg xserver-xorg x11-xserver-utils php5-curl luakit
chown -R :www-data /var/www/html
chmod -R 754 /var/www/html
chmod -R 770 /var/www/html/server_files
cp /var/www/html/server_files/xinitrc /boot/xinitrc
cp /var/www/html/server_files/rc.local /etc/rc.local
