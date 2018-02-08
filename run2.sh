#!/bin/bash
for i in `seq 16 21`
do
   j=$[$i * 100000]
   k=$[$j + 100000]
   php artisan sAdd:used_account_ids --min_offset=$j --max_offset=$k --appid=1318070822 &
done
