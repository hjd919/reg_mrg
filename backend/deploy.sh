#!/bin/bash
npm run build
scp -r /Users/hjd/dc/app/reg_mrg/backend/dist rdadmin@192.168.1.100:/home/rdadmin/dc/app/reg_mrg/backend