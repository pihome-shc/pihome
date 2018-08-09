#!/bin/bash
#=================================================================
# Script Variables Settings
clear
wlan='wlan0'
gateway='192.168.99.1'
alias ifup='/sbin/ifup'
alias ifdown='/sbin/ifdown'
alias ifconfig='/sbin/ifconfig'

# Where and what you want to call the Lockfile
lockfile='/var/www/cron/reboot_wifi.pid'

#=================================================================
echo "  _____    _   _    _                            "
echo " |  __ \  (_) | |  | |                           "
echo " | |__) |  _  | |__| |   ___    _ __ ___     ___ "
echo " |  ___/  | | |  __  |  / _ \  | |_  \_ \   / _ \ "
echo " | |      | | | |  | | | (_) | | | | | | | |  __/"
echo " |_|      |_| |_|  |_|  \___/  |_| |_| |_|  \___|"
echo " "
echo "    S M A R T   H E A T I N G   C O N T R O L "
echo "*************************************************************************"
echo "* PiHome is Raspberry Pi based Central Heating Control systems. It runs *"
echo "* from web interface and it comes with ABSOLUTELY NO WARRANTY, to the   *"
echo "* extent permitted by applicable law. I take no responsibility for any  *"
echo "* loss or damage to you or your property.                               *"
echo "* DO NOT MAKE ANY CHANGES TO YOUR HEATING SYSTEM UNTIL UNLESS YOU KNOW  *"
echo "* WHAT YOU ARE DOING                                                    *"
echo "*************************************************************************"
echo
echo "                                                           Have Fun - PiHome" 
date
echo " - Auto Reconnect Wi-Fi Status for $wlan Script Started ";
echo
echo "*************************************************************************"
date

echo 
echo "Starting WiFi check for $wlan"
echo 
# Check to see if there is a lock file
if [ -e $lockfile ]; then
    # A lockfile exists... Lets check to see if it is still valid
    pid=`cat $lockfile`
    # if kill -0 &>1 > /dev/null $pid; then
	if kill -0 &>1 $pid; then
        # Still Valid... lets let it be...
        echo "Process still running, Lockfile valid"
        exit 1
    else
        # Old Lockfile, Remove it
        echo "Old lockfile, Removing Lockfile"
        rm $lockfile
    fi
fi
# If we get here, set a lock file using our current PID#
echo "Setting Lockfile"
echo $$ > $lockfile

# We can perform check
echo "Performing Network check for $wlan"
# Only send two pings, sending output to /dev/null

ping -c2 ${gateway} #> /dev/null 
# If the return code from ping ($?) is not 0 (meaning there was an error)
if [ $? != 0 ]
then
    # Restart the wireless interface
    ip link set wlan0 down 
	#ifdown --force wlan0
	sleep 5
    #ifup wlan0
	ip link set wlan0 up 
fi
ping -I ${wlan} -c2 ${gateway} > /dev/null

# Check is complete, Remove Lock file and exit
#echo "process is complete, removing lockfile"
rm $lockfile
echo "Reboot WiFi Script Ended"
date
exit 0

##################################################################
# End of Script
##################################################################