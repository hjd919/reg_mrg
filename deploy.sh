#!/bin/bash
if [ $1 ]; then
git commit -a -m $1
fi
git push
cmd="cd dc/app/reg_mrg && git pull"
ssh rdadmin@192.168.1.100 ${cmd}