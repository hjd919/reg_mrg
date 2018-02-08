#!/bin/bash
for i in {4..6}
do
   j=`expr $i*100000`
   k=`expr $j+100000`
   php artisan sAdd:used_account_ids --min_offset=$j --max_offset=$k --appid=1318070822 &
done
