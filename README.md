# reg_mrg

http://jishua.yz210.com/storage/debs/reg_mrg.sql


* 启动casperjs服务
cd casperjs目录
docker run -d --name reg_ru -v $(pwd):/home/casperjs-tests --restart always reg_ru:1.0

* 启动抓取
docker exec reg_ru casperjs --web-security=no login_ty.js --email_name='f0f308'

# 统计每小时数据
SELECT date_format(successed_at,'%Y-%m-%d %H'),count(*) FROM `appleids` WHERE successed_at>'2018-04-3' group by date_format(successed_at,'%Y-%m-%d %H')

select sum(a) from (SELECT ip,count(*) a FROM `ips` group by ip) b where a >1  104

SELECT date_format(created_at,'%Y-%m-%d %H:%i'),count(*) a FROM `ips` group by date_format(created_at,'%Y-%m-%d %H:%i') order by a desc

每小时情况
SELECT date_format(updated_at,'%Y-%m-%d %H') a,state,count(*) FROM `appleids` where updated_at>'2018-04-18 04' group by a,state
每分钟情况
SELECT date_format(updated_at,'%Y-%m-%d %H:%i') a,state,count(*) FROM `appleids` where updated_at>'2018-04-18 17:45' group by a,state

每小时跑的数量
SELECT date_format(updated_at,'%Y-%m-%d %H') a,count(*) FROM `appleids` where updated_at>'2018-04-18 04' group by a
每分钟情况
SELECT date_format(updated_at,'%Y-%m-%d %H:%i') a,count(*) FROM `appleids` where updated_at>'2018-04-18 04' group by a

每小时chan数量
SELECT date_format(created_at,'%Y-%m-%d %H') a,count(*) FROM `appleids` where created_at>'2018-04-18 04' group by a
