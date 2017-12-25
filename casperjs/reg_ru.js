// casperjs --web-security=no --cookies-file=./casperjs/cookie.txt ./casperjs/reg_ru.js
// https://stackoverflow.com/questions/19199641/casperjs-download-csv-file
// https://www.icloud.com/
var utils = require('utils')
var process = require("child_process")
var fs = require('fs');
fs.removeTree('./casperjs/capture');

var execFile = process.execFile
console.log('stdout')
execFile("php", ["./artisan", "verify:capcha"], null, function (err, stdout, stderr) {
    console.log('jinlaile')
    console.log('stdout', stdout)
    return true
    if (stdout.indexOf('error') !== -1) {
        casper.echo(stdout, 'ERROR')
        return false
    }

    var capcha = stdout
    // 获取验证码
    casper.evaluate(function (capcha_code) {
        document.querySelector('.b-captcha__code').value = capcha_code
        document.querySelector('button[data-name="submit"]').click()
        // 截图
        casper.capture(get_pic_name(step));
        step++
    }, capcha)

    // 查看注册结果
    casper.evaluate(function (capcha_code) { })
})
phantom.exit()

var casper = require('casper').create({
    viewportSize: { width: 1024, height: 768 },
    pageSettings: {
        userAgent: 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.84 Safari/537.36',
        resourceTimeout: 6000,
        loadImages: false,        // The WebPage instance used by Casper will
        loadPlugins: false         // use these settings
    },
    logLevel: "info",
    verbose: true,
    // onWaitTimeout: function (timeout) {
    //     this.echo('error:onWaitTimeout' + timeout)
    // }
});
var mouse = require("mouse").create(casper);

// 获取截图名称
var step = 1;
var get_pic_name = function (step) {
    return './casperjs/capture/step_' + step + '.jpg'
}

// 注册页面
casper.start('https://account.mail.ru/signup/simple', function () {
    this.waitForSelector('div[data-field-name="email"]', function () {
        // 截图
        this.capture(get_pic_name(step));
        step++

        // # 填写登录信息
        this.evaluate(function () {
            document.querySelector('.b-dropdown__list__item.day2').click()
            document.querySelector('.b-date__month .b-dropdown__list__item[data-value="2"]').click()
            document.querySelector('.b-date__year .b-dropdown__list__item[data-value="1992"]').click()
            document.querySelector('label[data-mnemo="sex-female"]').click()

            // 密码
            document.querySelector('input[name="password"]').focus()
            document.querySelector('input[name="password"]').value = 'hujiande123321'
            document.querySelector('input[name="password_retry"]').value = 'hujiande123321'

            document.querySelector('input[name="firstname"]').value = 'mis1dk'
            document.querySelector('input[name="lastname"]').value = 'v3ase'
        })

        this.sendKeys('div[data-field-name="email"] span.b-email__name input', 'fas2weo234cvqq12')

        this.wait(10000, function () {
            this.evaluate(function () {
                document.querySelector('div[class*="b-form__control_main"] [data-blockid="btn"]').click()
            })
        })
    })
});

// 填写验证码页面
casper.waitForSelector('.b-captcha__code', function () {

    // 截图
    casper.capture(get_pic_name(step));
    step++

    // 下载验证码
    casper.download('https://c.mail.ru/c/6', '6.jpeg');

    execFile("php", ["artisan", "verfiy:capcha"], null, function (err, stdout, stderr) {
        if (stdout.indexOf('error') !== -1) {
            casper.echo(stdout, 'ERROR')
            return false
        }

        var capcha = stdout
        // 获取验证码
        casper.evaluate(function (capcha_code) {
            document.querySelector('.b-captcha__code').value = capcha_code
            document.querySelector('button[data-name="submit"]').click()
            // 截图
            casper.capture(get_pic_name(step));
            step++
        }, capcha)

        // 查看注册结果
        casper.evaluate(function (capcha_code) { })
    })
})


casper.run(function () {
    this.exit();
});