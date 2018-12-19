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
SELECT date_format(created_at,'%Y-%m-%d') a,count(*) FROM `appleids` where created_at>'2018-05-21' group by a
每小时成功量
SELECT date_format(created_at,'%Y-%m-%d') a,count(*) FROM `appleids` where created_at>'2018-05-21' and state=200 group by a
每天所需账号
SELECT date_format(start_time,'%Y-%m-%d'),sum(brushed_num) as total FROM `apps` WHERE `start_time` > '2018-05-20 00:00:00' group by date_format(start_time,'%Y-%m-%d')


<!-- 每天账号被封状态 -->
SELECT date_format(import_date,'%Y-%m-%d')a,valid_status,count(*) FROM `emails` where import_date>'2018-04-01' group by a,valid_status order by a desc

#复制一对一账号和设备
docker exec -it dc_fpm_1 reg_mrg/artisan CpAppleidDevice

docker exec -it dc_fpm_1 php reg_mrg/artisan reg:mail

https://login.inbox.lv/signup
curl 'https://login.inbox.lv/signup/check_username' -H 'Pragma: no-cache' -H 'Origin: https://login.inbox.lv' -H 'Accept-Encoding: gzip, deflate, br' -H 'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,zh-TW;q=0.7,sv;q=0.6,pl;q=0.5' -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.110 Safari/537.36' -H 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8' -H 'Accept: */*' -H 'Cache-Control: no-cache' -H 'X-Requested-With: XMLHttpRequest' -H 'Cookie: ssid=tc5q7281bib3iplrc5vls5r6mk; bxID=52945c09fae61c6da0.51543905; language=en; uid=%7B%22users%22%3A%5B%22i1s9xxhbx7%22%5D%2C%22isLoggedIn%22%3Afalse%7D; _ga=GA1.2.1898492745.1544157933; _gat_otherTracker=1; __gfp_64b=6A3H1FPwTBZuaaQwH_hP0o1mlKQ7zEYoj2f3zamlFrH.V7' -H 'Connection: keep-alive' -H 'Referer: https://login.inbox.lv/signup' --data 'username=test&userpin=' --compressed

{"showCaptcha":false,"captchaType":2,"flash":{"type":"danger","text":"Someone has already registered that username"}}

{"showCaptcha":false,"captchaType":2,"flash":{"type":"success","text":"Username is available"}}

curl 'https://login.inbox.lv/captcha/check' -H 'Pragma: no-cache' -H 'Origin: https://login.inbox.lv' -H 'Accept-Encoding: gzip, deflate, br' -H 'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,zh-TW;q=0.7,sv;q=0.6,pl;q=0.5' -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.110 Safari/537.36' -H 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8' -H 'Accept: */*' -H 'Cache-Control: no-cache' -H 'X-Requested-With: XMLHttpRequest' -H 'Cookie: ssid=tc5q7281bib3iplrc5vls5r6mk; bxID=52945c09fae61c6da0.51543905; language=en; uid=%7B%22users%22%3A%5B%22i1s9xxhbx7%22%5D%2C%22isLoggedIn%22%3Afalse%7D; _ga=GA1.2.1898492745.1544157933; _gat_otherTracker=1; __gfp_64b=6A3H1FPwTBZuaaQwH_hP0o1mlKQ7zEYoj2f3zamlFrH.V7' -H 'Connection: keep-alive' -H 'Referer: https://login.inbox.lv/signup' --data 'userpin=wocea&namespace=signup&iframe=false' --compressed

curl 'https://login.inbox.lv/signup' -H 'Connection: keep-alive' -H 'Cache-Control: max-age=0' -H 'Origin: https://login.inbox.lv' -H 'Upgrade-Insecure-Requests: 1' -H 'Content-Type: application/x-www-form-urlencoded' -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.80 Safari/537.36' -H 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8' -H 'Referer: https://login.inbox.lv/signup?go=portal' -H 'Accept-Encoding: gzip, deflate, br' -H 'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,zh-TW;q=0.7,sv;q=0.6,pl;q=0.5' -H 'Cookie: bxID=70195c10c4f48beab1.00066808; language=en; _ga=GA1.2.278850430.1544602870; __gfp_64b=TSoVuU1oocm.LtExekERs39J9OCvMsY_.Bv6wmw9wPH.p7; browser_history115b4dd907b09c15=2361102178%7C1576150003; bx_h5v=1; bx_f=0; ssid=btlv2rkeq864oq9mn59hrsrfr6; uid=%7B%22users%22%3A%5B%22hjd321hjd%40inbox.lv%22%5D%2C%22isLoggedIn%22%3Afalse%7D; __gads=ID=d1a35e4c114e5a8c:T=1544614206:S=ALNI_MYctkYrqUdwSyaoNB0rLDHdbYQKpA; _gat_otherTracker=1' --data 'signup%5Buserpin%5D=tiand&g-recaptcha-response=03AO9ZY1DaE5ndVZb461Jx6q6ceQqKOMJXcZmmTWF_aO7C8ooY-X9N5NAOQ-0d3tV4Bf8TJGgtuYld1VCTpfKxlWCzE3rX0fhS6Zja_LFfBeX1AtfiZ-E3ThNp1BPqAPjoP_lkpOo2JKID1Y7_MC6XDVKlGDdRlZvK09ojJMqkKxD9vV1ZOVYFKMLu0BAZ94aFGrIjdpiC67AYFmBcd0bvQIMZGVH4isjOy07IskR-47LLdKpxg5a6uJxjaIy0M24cIv0wurAgGBOtNFGrfPSe-GJ89-X8hx_xhQ&product=login&redirect_url=https%3A%2F%2Flogin.inbox.lv%2Fprofile%2Foverview&grecaptcha_v3_token=03AO9ZY1B3C4gQYsbTRfe0iGuHzFK85bNmeeQEactGSrJlP7fvehkV72ajOpugc0RhdT6-Qq90pOLjRWV-OlYgZumVrVt02sWdr7BfA9RFkXpm6FtNeSMsYxM33FkK_Au6mQ-_-ooN9o9_ekmZU-2ZgG368VTkogvRRnwQejBilg94xp_lAI43A5Od29ncsDoEr1Uvoy2YlE26LA4HBJcdI6jRBv4GzH_tMc2VDJhenH2JVhjBiwXUoL5CFU1zI71dnaBhoqRSRrLYWDCFcmsS7ZNCWSWcjxI56Fn0aHag0elquxJ-ipz7FGk&signup%5B_token%5D=Ik6PWKsGfOFlmGrXmCcL1LanrsjWg-GoSeolGzo6wZ4&signup%5Bgender%5D=&signup%5Bbirthday%5D=&signup%5BprofilePicture%5D=&signup%5Buser%5D=hjd111hjd&signup%5Bforename%5D=hu&signup%5Bsurname%5D=dawei&signup%5Bpassword%5D%5Bpassword%5D=hjd825601&signup%5Bpassword%5D%5BpasswordRepeat%5D=hjd825601&signup%5Bprivacy%5D=1&signup%5Btos%5D=1' --compressed

signup[userpin]: tiand
g-recaptcha-response: 03AO9ZY1DaE5ndVZb461Jx6q6ceQqKOMJXcZmmTWF_aO7C8ooY-X9N5NAOQ-0d3tV4Bf8TJGgtuYld1VCTpfKxlWCzE3rX0fhS6Zja_LFfBeX1AtfiZ-E3ThNp1BPqAPjoP_lkpOo2JKID1Y7_MC6XDVKlGDdRlZvK09ojJMqkKxD9vV1ZOVYFKMLu0BAZ94aFGrIjdpiC67AYFmBcd0bvQIMZGVH4isjOy07IskR-47LLdKpxg5a6uJxjaIy0M24cIv0wurAgGBOtNFGrfPSe-GJ89-X8hx_xhQ
product: login
redirect_url: https://login.inbox.lv/profile/overview
grecaptcha_v3_token: 03AO9ZY1B3C4gQYsbTRfe0iGuHzFK85bNmeeQEactGSrJlP7fvehkV72ajOpugc0RhdT6-Qq90pOLjRWV-OlYgZumVrVt02sWdr7BfA9RFkXpm6FtNeSMsYxM33FkK_Au6mQ-_-ooN9o9_ekmZU-2ZgG368VTkogvRRnwQejBilg94xp_lAI43A5Od29ncsDoEr1Uvoy2YlE26LA4HBJcdI6jRBv4GzH_tMc2VDJhenH2JVhjBiwXUoL5CFU1zI71dnaBhoqRSRrLYWDCFcmsS7ZNCWSWcjxI56Fn0aHag0elquxJ-ipz7FGk
signup[_token]: Ik6PWKsGfOFlmGrXmCcL1LanrsjWg-GoSeolGzo6wZ4
signup[gender]: 
signup[birthday]: 
signup[profilePicture]: 
signup[user]: hjd111hjd
signup[forename]: hu
signup[surname]: dawei
signup[password][password]: hjd825601
signup[password][passwordRepeat]: hjd825601
signup[privacy]: 1
signup[tos]: 1


curl 'https://login.inbox.lv/profile/pass_recovery/question' -H 'Connection: keep-alive' -H 'Pragma: no-cache' -H 'Cache-Control: no-cache' -H 'Origin: https://login.inbox.lv' -H 'Upgrade-Insecure-Requests: 1' -H 'Content-Type: application/x-www-form-urlencoded' -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.110 Safari/537.36' -H 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8' -H 'Referer: https://login.inbox.lv/profile/pass_recovery/question' -H 'Accept-Encoding: gzip, deflate, br' -H 'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,zh-TW;q=0.7,sv;q=0.6,pl;q=0.5' -H 'Cookie: bxID=52945c09fae61c6da0.51543905; language=en; _ga=GA1.2.1898492745.1544157933; __gfp_64b=6A3H1FPwTBZuaaQwH_hP0o1mlKQ7zEYoj2f3zamlFrH.V7; ssid=spcv6p8glcd6631oq1iqlc5dmj; browser_historye86a4be2664da205=3932394642%7C1575694137; uid=%7B%22users%22%3A%5B%22hjd123hjd%40inbox.lv%22%5D%2C%22isLoggedIn%22%3Atrue%7D; _gat_otherTracker=1' --data 'pass_recovery_question_form%5Bquestion%5D=What+is+your+pet%27s+name%3F&pass_recovery_question_form%5Buser_question%5D=&pass_recovery_question_form%5Banswer%5D=xiaohu&pass_recovery_question_form%5B_token%5D=3NUrQfGUtfFdKNTfn_kBiXpgYkSBZdrYLlEsno4AhGg' --compressed

curl 'https://mail.inbox.lv/prefs/update' -H 'Connection: keep-alive' -H 'Pragma: no-cache' -H 'Cache-Control: no-cache' -H 'Origin: https://mail.inbox.lv' -H 'Upgrade-Insecure-Requests: 1' -H 'Content-Type: application/x-www-form-urlencoded' -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.110 Safari/537.36' -H 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8' -H 'Referer: https://mail.inbox.lv/prefs?group=forward' -H 'Accept-Encoding: gzip, deflate, br' -H 'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,zh-TW;q=0.7,sv;q=0.6,pl;q=0.5' -H 'Cookie: bxID=52945c09fae61c6da0.51543905; language=en; _ga=GA1.2.1898492745.1544157933; __gfp_64b=6A3H1FPwTBZuaaQwH_hP0o1mlKQ7zEYoj2f3zamlFrH.V7; bx_h5v=1; bx_f=0; bxID=52945c09fae61c6da0.51543905; _pubcid=57aed6b6-3001-4345-b476-2662073ec622; _gid=GA1.2.1289382872.1544165901; __gads=ID=85eb66a86e179ef0:T=1544166083:S=ALNI_MZYudchWyyyog7nXNPAz-ATxCXBgQ; googtrans=/en/zh-CN; _gat_otherTracker=1; ssid=spv6hi72l0vep6m76d9f20vjje; browser_historye86a4be2664da205=3932394642%7C1575705393; uid=%7B%22users%22%3A%5B%22hjd123hjd%40inbox.lv%22%5D%2C%22isLoggedIn%22%3Atrue%7D; inxFolderState=visible; ibb_euconsent=BOYZZVzOYZZVzABABBENBR-AAAAeCAMAAUAA0ACAAIAAWgAyABoAEUAJgAUQAtgD9A' --data 'csrf_token=lcCbLPepT1ArkrI8-ji4kllxRmFjPYpGz5nzxfUnFZQ&group=forward&stay=1&enable_pop3=on&mobile=&address=' --compressed


hjd123hjd
hjd321hjd