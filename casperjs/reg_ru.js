// casperjs --web-security=no ./casperjs/reg_ru.js --cookies-file=./casperjs/cookie.txt
// https://stackoverflow.com/questions/19199641/casperjs-download-csv-file
// https://www.icloud.com/

// var utils = require('utils')
var process = require("child_process")
var execFile = process.execFile
var spawn = process.spawn
var fs = require('fs');
fs.removeTree('./casperjs/capture');

var casper = require('casper').create({
    viewportSize: { width: 1024, height: 768 },
    pageSettings: {
        userAgent: 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.84 Safari/537.36',
        // resourceTimeout: 6000,
        //loadImages: false,        // The WebPage instance used by Casper will
        loadPlugins: false         // use these settings
    },
    logLevel: "info",
    verbose: true,
    // onWaitTimeout: function (timeout) {
    //     this.echo('error:onWaitTimeout' + timeout)
    // }
});
casper.on("remote.message", function (msg) {
    this.echo("remote.message: " + msg);
});

// 获取截图名称
var step = 1;
var get_pic_name = function (step) {
    return './casperjs/capture/step_' + step + '.jpg'
}

// 注册页面
// casper.start('https://www.baidu.com', function () {
casper.start('https://account.mail.ru/signup/simple', function () {
    this.evaluate(function () {
        document.body.bgColor = 'white';
    });

    // 截图
    casper.capture(get_pic_name(step));
    step++

    // casper.waitForSelector('div[data-field-name="email"]', function () {

    // # 填写登录信息
    casper.evaluate(function () {

        document.querySelector('.b-dropdown__list__item.day2').click()
        document.querySelector('.b-date__month .b-dropdown__list__item[data-value="2"]').click()
        document.querySelector('.b-date__year .b-dropdown__list__item[data-value="1992"]').click()
        document.querySelector('label[data-mnemo="sex-female"]').click()

        // 密码
        document.querySelector('input[name="password"]').focus()
        document.querySelector('input[name="password"]').value = 'hujiande123321'
        document.querySelector('input[name="password_retry"]').value = 'hujiande123321'

        document.querySelector('input[name="firstname"]').value = 'ier241ddw'
        document.querySelector('input[name="lastname"]').value = 'ddx112ewf'
    })

    this.sendKeys('div[data-field-name="email"] span.b-email__name input', 'juitt345ffg33')

    // 等待名字检测完成
    this.wait(5000, function () {

        // 截图
        casper.capture(get_pic_name(step));
        step++

        this.evaluate(function () {
            document.querySelector('div[class*="b-form__control_main"] [data-blockid="btn"]').click()
        })
    })
    // })
});

var img_path = './casperjs/capture/capcha.png'

// 填写验证码页面
casper.waitForSelector('.b-captcha__captcha', function () { //等选择器内容找到后,

    casper.wait(5000, function () {
        // 截图
        this.capture(get_pic_name(step));
        step++

        this.captureSelector(img_path, '.b-captcha__captcha');//对指定元素截图 

        // 从子进程中获取验证码结果
        var child = spawn("php", ["./artisan", "verify:capcha"])
        var capcha = ''
        child.stdout.on("data", function (data) {
            if (data == 'error' || !data) {
                console.log('error', data)
                phantom.exit()
            }

            console.log('data--' + data)

            // 填写验证码
            casper.evaluate(function (code) {
                console.log('data', code)
                if (!code){
                    document.querySelector('[name="capcha"]').value = '1111'
                }else{
                    document.querySelector('[name="capcha"]').value = code
                }
                document.querySelector('button[data-name="submit"]').click()
            }, data)

            // 2.子进程结束了
            casper.wait(1000, function () {
                // 截图
                casper.capture(get_pic_name(999));
                step++

                phantom.exit()
            })
        })

        casper.wait(20000) // 等待子进程结束
    })

    console.log('capture2222')
});

casper.run();