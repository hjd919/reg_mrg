#!/bin/bash  
  
for((i=1;i<=10;i++));  
do   
php artisan jia:device > ./jiadevice.txt
echo $(expr $i \* 3);  
done 
