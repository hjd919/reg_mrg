#!/bin/bash
git commit -a -m $1
git push
cmd="cd jishua_api && git pull && chown -R nobody:nobody *"
ssh hjd@60.205.58.24 ${cmd}
ssh hjd@101.201.28.127 ${cmd}
