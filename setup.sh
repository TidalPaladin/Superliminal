#!/bin/bash

# Function to edit config.txt and make replacements
# Pass $1 as setting name and $2 as new value
function editConfig {
	if [[ $(grep "$1" /boot/config.txt) ]]; then
        	sed -i "s/.*$1.*/$1=$2/" /boot/config.txt
		echo "Replacing $1=$2 in config.txt"
	else
        	echo "$1=$2" >> /boot/config.txt
	fi
}

function editLXDE {
        if [[ $(grep "$1" /home/pi/.config/lxsession/LXDE-pi/autostart) ]]; then
                sed -i "s/.*$1.*/$2/" /home/pi/.config/lxsession/LXDE-pi/autostart 
                echo "Replacing $1 in LXDE autostart"
        else
                echo "$2" >> /home/pi/.config/lxsession/LXDE-pi/autostart
		echo "Adding new line for '$1'"
        fi
}



# Install new packages and setup file permissions
apt-get install -y php5 php5-curl iceweasel
mkdir /var/www/html/flyers
chown -R :www-data /var/www/html
chmod -R 755 /var/www/html
chmod 775 /var/www/html/server_files/accounts.json /var/www/html/server_files/settings.ini
chmod 775 /var/www/html/server_files
chown -R root:root /var/www/html/system_files
chmod -R 755 /var/www/html/system_files


# Edit sudoers file
sed -i '/^#SUPERLIMINAL/,/^\#END/d' /etc/sudoers
echo '#SUPERLIMINAL suoders requirements' >> /etc/sudoers
echo 'www-data ALL=NOPASSWD: /var/www/html/server_files/reboot.sh' >> /etc/sudoers
echo 'www-data ALL=NOPASSWD: /var/www/html/server_files/scan.sh' >> /etc/sudoers
echo 'www-data ALL=NOPASSWD: /var/www/html/server_files/overscan.sh' >> /etc/sudoers
printf "#END SUPERLIMINAL suoders requirements\r\n" >> /etc/sudoers

# Edit rc.local
cp /var/www/html/system_files/rc.local /etc 
cp /var/www/html/system_files/000-default.conf /etc/apache2/sites-available/

# Edit LXDE to autostart firefox
editLXDE 'firefox' '@firefox 127.0.0.1/startup.php'

# Edit LXDE to disable screen blanking
editLXDE 'xset s off' '@xset s off'
editLXDE 'xset -dpms' '@xset -dpms'
editLXDE 'xset s noblank' '@xset s noblank'
sed -i '/xscreensaver/d' /home/pi/.config/lxsession/LXDE-pi/autostart

# Edit LXDE to autostart teamviewer script
editLXDE 'teamviewer' '@bash /var/www/html/scripts/teamviewer_autoclose.sh'

#Edit config.txt with necessary sections

# disable_overscan=1
editConfig 'disable_overscan' '1'

# hdmi_force_hotplug=1
editConfig 'hdmi_force_hotplug' '1'

# Framebuffer width and height
editConfig 'framebuffer_width' '1920'
editConfig 'framebuffer_height' '1080'

# Bootcode delay
editConfig 'bootcode_delay' '5'

echo "Setup complete, please reboot"
