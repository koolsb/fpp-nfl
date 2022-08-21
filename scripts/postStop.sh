#!/bin/sh

kill `ps aux | grep nfl | grep -v grep | awk '{print $2}'`

