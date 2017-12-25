// 注册页，填写信息
// 保存验证码，提交验证码

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
        userAgent:'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.84 Safari/537.36',
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

// 获取截图名称
var step = 1;
var get_pic_name = function (step) {
    return './casperjs/capture/step_' + step + '.jpg'
}

casper.start('https://account.mail.ru/signup/simple',function(){
    // 截图
    this.capture(get_pic_name(step));
    step++
    this.thenOpen('https://account.mail.ru/signup/simple')
})

// casper.page.onConsoleMessage = function () {
//     console.log("page.onConsoleMessage");
//     printArgs.apply(this, arguments);
// };

// 注册页面
casper.then(function () {
    this.capture(get_pic_name(step));
    step++

    // # 填写登录信息
    this.evaluate(function () {
        document.querySelector('input[name="password"]').focus()

        // document.querySelector('div[data-field-name="firstname"] input').click()
        // document.querySelector('input[name="firstname"]').value = 'mis1dk'
        // document.querySelector('div[data-field-name="lastname"] input').click()
        // document.querySelector('input[name="lastname"]').value = 'v3ase'
    

        // document.querySelector('.b-dropdown__list__item.day2').click()
        // document.querySelector('.b-date__month .b-dropdown__list__item[data-value="2"]').click()
        // document.querySelector('.b-date__year .b-dropdown__list__item[data-value="1992"]').click()
    
        // document.querySelector('label[data-mnemo="sex-female"]').click()
    
    })
    // this.sendKeys('div[data-field-name="password"] input', 'fda2342sfdasdfa23423')

    this.evaluate(function () {
        document.querySelector('input[name="password_retry"]').click()
    })


    this.evaluate(function () {
        document.querySelector('div[class*="b-form__control_main"] [data-blockid="btn"]').click()
        // 截图
        this.capture(get_pic_name(step));
        step++
    })
});

// 填写验证码页面
casper.then(function () {
    // 截图
    this.capture(get_pic_name(step));
    step++
})

casper.run(function () {
    this.exit();
});