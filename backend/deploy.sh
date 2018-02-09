#!/bin/bash
npm run build
rsync -avzP \
--password-file=/Users/jdhu/rsync/rsync.pwd \
/Users/jdhu/work/backend/dist/* \
rsync@60.205.58.24::jishua_backend