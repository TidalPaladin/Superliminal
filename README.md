# Superliminal
Superliminal is a digital signage solution designed to provide private and commercial users with a cost effective, customizable, and always-on solution for viewing images on a TV.

## Key Features
  * **Simple User Interface** - 
  Superliminal's web interface allows for easy setup without requiring the user to access command line interfaces.  During startup a keyboard may be used to access a setup page, where all of Superliminal's options are accessible.

  * **Dropbox Integration** - 
  Easily connect a Dropbox account (other cloud services planned for the future) to effortlessly update signage materials.

  * **Remote Management via Browser** - 
  Often the hardware used to drive digital signage is mounted in a difficult to access location.  For network enabled installations, Superliminal offers configuration via a web browser (similar to setting up a router).  Superliminal's options are protected by a customizable password.

  * **Failsafe Hotspot** - 
  If enabled by the user, Superliminal can respond to an inaccessible local network by defaulting to an access point.  The SSID and (WPA) passphrase for the hotspot are configurable by the user.  The user can then connect to hardware running Superliminal like any other wireless network, allowing for remote management through a web browser.  This is designed to eliminate the cumbersome need to directly access signage hardware.

  * **USB Mode** - 
  For the most simplistic setup possible, images can be loaded into Superliminal using a USB flash drive.  No network connectivity or cloud account required, just load images onto a flash drive and boot.

  * **Easy Overscan Adjustment** - 
  All TVs are different, and Superliminal accounts for this by making overscan adjustments easy.  It only takes one reboot to enable overscan, from there different overscan values can be tested live - no need to reboot after each trial.

  * **Designed with Deployment in Mind** - 
  With Superliminal it is possible to ready a single image for deployment to several locations.

## Installation
Superliminal was designed and tested on Raspbian Jessie, no other operating systems have been tested at this point.  It is recommended to start with a clean install of Jessie Lite or Jessie.  Superliminal will need to make changes to `rc.local, xinitrc, sudoers` in order to function properly.  If you have made changes to these files that you would like to perserve, please use manual setup.

### Automated Install
  Superliminal includes a script `namehere.sh` that will automate installation on a fresh operating system.  The script handles installation of required packages

  * **Install Required Packages** -
    ```
    apt-get update
    
    apt-get install apache2 php5 network-manager fbi unclutter matchbox-window-man xorg xserver-xorg php5-curl luakit
    ```
  * **Clone**
    ```
    cd /var/www/html
    
    git clone https://github.com/TidalPaladin/Superliminal.git
    ```
