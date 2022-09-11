#!/bin/bash

# fpp-nfl install script

# Mark to reboot
/opt/fpp/scripts/update_plugin ${target_PWD##*/}
echo ; echo “Please reboot fppd” ; echo
. /opt/fpp/scripts/common
setSetting rebootFlag 1
popd
