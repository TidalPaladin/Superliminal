#!/bin/bash

apt-get install -y apache2 php5 network-manager fbi unclutter matchbox xorg xserver-xorg x11-xserver-utils php5-curl luakit
chown -R :www-data /var/www/html
chmod -R 755 /var/www/html
chmod 775 /var/www/html/server_files/accounts.json /var/www/html/server_files/settings.ini
chown -R root:root html/system_files
chmod -R 755 html/system_files

cp /var/www/html/system_files/xinitrc /boot
cp /var/www/html/system_files/rc.local /etc
cp /var/www/html/system_files/000-default.conf /etc/apache2/sites-available/

echo 'www-data ALL=NOPASSWD: /usr/bin/nmcli' >> /etc/sudoers
echo 'www-data ALL=NOPASSWD: /var/www/html/server_files/overscan' >> /etc/sudoers
echo 'www-data ALL=NOPASSWD: /var/www/html/server_files/reboot.sh' >> /etc/sudoers
echo 'www-data ALL=NOPASSWD: /bin/mount' >> /etc/sudoers
echo 'www-data ALL=NOPASSWD: /var/www/html/server_files/scan.sh' >> /etc/sudoers
