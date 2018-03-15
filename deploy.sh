#!/bin/bash
if [ $1 ]; then
git commit -a -m $1
fi
git push
cmd="cd jishua_api && git pull"
ssh rdadmin@192.168.1.100 ${cmd}