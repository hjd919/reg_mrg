# reg_mrg

http://jishua.yz210.com/storage/debs/reg_mrg.sql


* 启动casperjs服务
cd casperjs目录
docker run -d --name reg_ru -v $(pwd):/home/casperjs-tests --restart always reg_ru:1.0

* 启动抓取
docker exec reg_ru casperjs --web-security=no login_ty.js --email_name='f0f308'