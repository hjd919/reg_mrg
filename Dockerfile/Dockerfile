FROM reg_ru:latest

RUN ln -sf /usr/share/zoneinfo/Asia/Shanghai /etc/localtime

WORKDIR /home/casperjs-tests

# reset from parent image
ENTRYPOINT []

# run the image as daemon
CMD echo "casperjs running..." && tail -f /dev/null

