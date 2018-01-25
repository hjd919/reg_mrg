# jishua_api

docker run -d --name reg_ru -v /Users/jdhu/work/jishua_api/reg_ru:/home/casperjs-tests --restart always reg_ru:1.0

docker run -d --name reg_ru -v /Users/jdhu/work/jishua_api/reg_ru:/home/casperjs-tests --restart always reg_ru
docker run -d --name reg_ru -v /root/casperjs:/home/casperjs-tests --restart always reg_ru

docker exec reg_ru casperjs  --web-security=no --cookies-file=./cookie.txt reg_ru.js