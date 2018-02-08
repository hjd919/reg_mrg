#!/bin/sh
for i in `seq 0 7`
do
 min_i=$[i * 100000]
 max_i=$[min_i + 100000]
echo $min_i--$max_i
 php artisan migrate:redis --max_i=${max_i} --min_i=${min_i} --session_id=7b6hgufhau6k725j58d9kass81&


#!/bin/bash
for i in {4..6}
do
   j=`expr $i*100000`
   k=`expr $j+100000`
   php artisan sAdd:used_account_ids --min_offset=$j --max_offset=$k --appid=1318070822 &
done
