#!/bin/bash

pushd $(dirname $(which $0))
target_PWD=$(readlink -f .)
/opt/fpp/scripts/update_plugin ${target_PWD##*/}
echo ; echo “Please reboot fppd.” ; echo
. /opt/fpp/scripts/common
setSetting rebootFlag 1
popd
