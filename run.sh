#!/bin/sh
for i in `seq 0 7`
do
 min_i=$[i * 100000]
 max_i=$[min_i + 100000]
echo $min_i--$max_i
 php artisan migrate:redis --max_i=${max_i} --min_i=${min_i} --session_id=7b6hgufhau6k725j58d9kass81&
done
