#!/bin/bash

# fpp-nfl install script

# Mark to reboot
sed -i -e "s/^restartFlag .*/restartFlag = 1/" /home/fpp/media/settings
/opt/fpp/scripts/update_plugin ${target_PWD##*/}
echo ; echo “Please reboot fppd” ; echo
. /opt/fpp/scripts/common
setSetting rebootFlag 1
