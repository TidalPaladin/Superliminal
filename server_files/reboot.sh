#!/bin/bash
source /var/www/html/server_files/settings.ini

# Get overscan values in config.txt
        overscan_config=$(cat /boot/config.txt | grep -i disable_overscan= | sed 's/disable_overscan=//g')
        overscan_x_config=$(cat /boot/config.txt | grep -i overscan_right= | sed 's/overscan_right=//g')
        overscan_y_config=$(cat /boot/config.txt | grep -i overscan_top= | sed 's/overscan_top=//g')

        # If settings.ini has different values than config.txt, update and reboot

        if [ $overscan == 'enabled' ]; then
                sudo sed -i "s/.*disable_overscan.*/#disable_overscan=1/g" /boot/config.txt
                sudo sed -i "s/.*overscan_left.*/overscan_left=$overscan_x/g" /boot/config.txt
                sudo sed -i "s/.*overscan_right.*/overscan_right=$overscan_x/g" /boot/config.txt
                sudo sed -i "s/.*overscan_top.*/overscan_top=$overscan_y/g" /boot/config.txt
                sudo sed -i "s/.*overscan_bottom.*/overscan_bottom=$overscan_y/g" /boot/config.txt

        else
                sudo sed -i "s/.*disable_overscan.*/disable_overscan=1/g" /boot/config.txt
                sudo sed -i "s/.*overscan_left.*/#overscan_left=$overscan_x/g" /boot/config.txt
                sudo sed -i "s/.*overscan_right.*/#overscan_right=$overscan_x/g" /boot/config.txt
                sudo sed -i "s/.*overscan_top.*/#overscan_top=$overscan_y/g" /boot/config.txt
                sudo sed -i "s/.*overscan_bottom.*/#overscan_bottom=$overscan_y/g" /boot/config.txt
        fi

sudo reboot
