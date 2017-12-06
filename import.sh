## 复制到服务器，并执行服务器导入脚本

#!/bin/bash

#获取命令行参数
##判断输入
##if [ "$1" == '' ];then
if [ ! $1 ];then
echo 'import.sh {filename=appleid_1206.txt} {cmd=import:appleids}'
exit
fi
filename=$1
php_cmd=$2

filepath=/Users/jdhu/Downloads/${filename}

#复制到服务器
scp $filepath hjd@60.205.58.24:~/jishua_api

#登录服务器，并执行导入脚本
ssh hjd@60.205.58.24 "cd jishua_api && php artisan ${php_cmd} --file=${filename}"

