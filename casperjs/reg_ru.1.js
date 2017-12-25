// casperjs --web-security=no --cookies-file=./casperjs/cookie.txt ./casperjs/reg_ru.js
// https://stackoverflow.com/questions/19199641/casperjs-download-csv-file
// https://www.icloud.com/
var utils = require('utils')
var process = require("child_process")
var execFile = process.execFile

var casper = require('casper').create({
    viewportSize: { width: 1024, height: 768 },
    pageSettings: {
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

var step = 1;

// 获取命令行的输入参数

casper.on("remote.message", function (msg) {
    this.echo("remote.message: " + msg);
});

// 获取截图名称
var get_pic_name = function (step) {
    return './casperjs/capture/step_' + step + '.jpg'
}

// 注册页面
casper.start('https://account.mail.ru/signup/simple', function () {


    // # 等待登录界面
    casper.then(function () {

        // # 填写登录信息
        this.evaluate(function () {
            // 某些信息不能传统赋值
            // document.querySelector('input[name="firstname"]').value = 'mis1dk'

            // document.querySelector('input[name="lastname"]').value = 'v3ase'

            document.querySelector('.b-dropdown__list__item.day2').click()
            document.querySelector('.b-date__month .b-dropdown__list__item[data-value="2"]').click()
            document.querySelector('.b-date__year .b-dropdown__list__item[data-value="1992"]').click()

            document.querySelector('label[data-mnemo="sex-female"]').click()

            // document.querySelector('div[data-field-name="email"] input').focus()
            // document.querySelector('div[data-field-name="email"] input[data-blockid="email_name"]').value = 'ja2ck231join653ww'
            // document.querySelector('div[data-field-name="email"] input[data-bem="b-input"]').value = 'ja2ck231join653ww'
            // document.querySelector('div[data-field-name="email"] input').blur()
            // $('div[data-field-name="email"] input[data-blockid="email_name"]').val('eee')
        })

        this.click('div[data-field-name="firstname"]')
        this.sendKeys('div[data-field-name="firstname"] input', 'misdk')
        this.click('div[data-field-name="lastname"]')
        this.sendKeys('div[data-field-name="lastname"] input', 'vse')
        
        this.capture(get_pic_name(step));
        step++

        // 输入邮箱
        this.sendKeys('div[data-field-name="email"] span.b-email__name input', 'ja2ck231join653ww')

        // 截图
        this.capture(get_pic_name(step));
        step++

        // 密码
        this.click('div[data-field-name="password"] .b-form-field__input')
        this.sendKeys('div[data-field-name="password"] input', 'Hufdsa234fdsa')

        this.capture(get_pic_name(step));
        step++

        this.sendKeys('div[data-field-name="password_retry"] input', 'Hufdsa234fdsa')

        // 截图 重复密码出现
        this.capture(get_pic_name(step));
        step++

        this.evaluate(function () {

            // document.querySelector('input[name="password"]').click()
            // document.querySelector('input[name="password"]').value = 'Hujiande762'

            document.querySelector('div[class*="b-form__control_main"] [data-blockid="btn"]').click()

            // document.querySelector('input[name="password_retry"]').value = 'Hujiande762'
        })
    })
});

var our_keywords_links
var keyword_ranks = []

// 填写验证码页面
casper.then(function () {
    // 截图
    this.capture(get_pic_name(step));
    step++
})

// casper.waitForText('Укажите код с картинки',function () {
//     // 获取验证码
//     var capcha = ''
//     var test = execFile("ls", ["-lF", "./"], null, function (err, stdout, stderr) {
//         capcha = 5555
//         // 填写验证码登录
//         // console.log("execFileSTDOUT:", JSON.stringify(stdout))
//         return capcha
//     })
//     console.log("capcha:", test)
// })

casper.run(function () {
    // { [id, after_rank, on_rank_time, on_rank_start, on_rank_end,ranks] }
    this.exit();
});