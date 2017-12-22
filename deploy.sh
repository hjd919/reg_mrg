#!/bin/bash
git push
cmd="cd jishua_api && git pull"
ssh hjd@60.205.58.24 ${cmd}
ssh hjd@101.201.28.127 ${cmd}
