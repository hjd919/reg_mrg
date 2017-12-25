// casperjs --web-security=no --cookies-file=./casperjs/cookie.txt ./casperjs/reg_ru.js
// https://stackoverflow.com/questions/19199641/casperjs-download-csv-file
// https://www.icloud.com/
var utils = require('utils')
var process = require("child_process")
var fs = require('fs');
fs.removeTree('./casperjs/capture');

var execFile = process.execFile

var casper = require('casper').create({
    viewportSize: { width: 1024, height: 768 },
    pageSettings: {
        userAgent: 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.84 Safari/537.36',
        resourceTimeout: 4000,
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
    // 截图
    this.capture(get_pic_name(step));
    step++

    // # 填写登录信息
    this.evaluate(function () {

        document.querySelector('input[name="firstname"]').value = 'mis1dk'
        document.querySelector('input[name="lastname"]').value = 'v3ase'
        document.querySelector('.b-dropdown__list__item.day2').click()
        document.querySelector('.b-date__month .b-dropdown__list__item[data-value="2"]').click()
        document.querySelector('.b-date__year .b-dropdown__list__item[data-value="1992"]').click()
        document.querySelector('label[data-mnemo="sex-female"]').click()

        document.querySelector('div[data-field-name="email"] input').focus()
        document.querySelector('div[data-field-name="email"] span.b-email__name input').value = 'ja2c2k3231join653ww'
    })
    var res
    res = this.mouse.click('input[name="password"]');
    res = this.mouse.click('div[data-field-name="email"] span.b-email__name input');
    res = this.sendKeys('div[data-field-name="email"] span.b-email__name input', 'ja2c2k3231join653ww')

    this.evaluate(function () {
        document.querySelector('input[name="password"]').value = 'fwewe2234sdf'
        document.querySelector('input[name="password_retry"]').value = 'fwewe2234sdf'
        document.querySelector('div[class*="b-form__control_main"] [data-blockid="btn"]').click()
    })
});

// 填写验证码页面
casper.then(function () {
    this.wait(5000, function () {
        // 截图
        this.capture(get_pic_name(step));
        step++
    })
})

casper.run(function () {
    this.exit();
});