
var fs = require('fs');
fs.removeTree('./capture');// 删除截图

var spawn = require("child_process").spawn

var casper = require('casper').create({
    viewportSize: { width: 1920, height: 1080 },
    pageSettings: {
        userAgent: 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.84 Safari/537.36',
        resourceTimeout: 30000,
        // loadImages: false,        // The WebPage instance used by Casper will
        // loadPlugins: false         // use these settings
    },
    logLevel: "info",
    verbose: true,
    waitTimeout: 30000,
    // onWaitTimeout: function (timeout) {
    //     this.echo('error:onWaitTimeout' + timeout)
    // }
});


var mouse = require("mouse").create(casper);

// 打印远端信息
casper.on("remote.message", function (msg) {
    this.log("remote.message: " + msg, 'error');
});

// 截图
function capture(title) {
    var timestamp = (new Date()).valueOf();
    casper.capture('./capture/' + timestamp + title + '.jpg');
}

casper.start('https://login.inbox.lv/signup?go=portal', function () {
    this.evaluate(function () {
        document.body.bgColor = 'white';
    });
    this.log('注册', 'info');

    // 填写用户名
    fillUserName()

}).waitForSelector('#signup-alerts .alert-success', function () {
    this.log('验证用户名成功', 'info')

    // 填写密码和姓名
    fillPassword()

    // 点击协议
    clickProtocol()
}).waitForSelector('#signup_submit', function () {
    this.log('提交表单', 'info')
    capture('提交表单')
    submitForm()
}).waitForResource(function testResource(resource) {
    return resource.url.indexOf("captcha2") > 0;
}, function onReceived() {
    this.log('获取图片验证码', 'info')
    captureCaptcha()
}).then(function () {
    this.log('校验图片验证码', 'info')
    verifyCaptcha()
}).wait(20000, function () {
    this.log('最后一步1', 'info')
    capture('最后一步')
}).run()


// 填写用户名
function fillUserName() {
    casper.log('填写用户名', 'info')
    casper.evaluate(function () {
        document.querySelector('input[name="signup[user]"]').value = 'hjd323hjd'
        document.querySelector('#check-uname').click()
    })
    casper.log('验证用户名', 'info')
    capture('验证用户名');
}

// 填写密码和姓名
function fillPassword() {
    casper.log('填写密码和姓名', 'info')
    casper.evaluate(function () {
        document.querySelector('input[name="signup[forename]"]').value = 'hu'
        document.querySelector('input[name="signup[surname]"]').value = 'jianquan'
        document.querySelector('input[name="signup[password][password]"]').value = 'hjd825601'
        document.querySelector('input[name="signup[password][passwordRepeat]"]').value = 'hjd825601'
    })
}

// 点击协议
function clickProtocol() {
    casper.log('点击协议', 'info')
    for (var i = 0; i < 15; i++) {
        casper.wait(100, function () {
            mouse.move("#btn-privacy-scroll");
            mouse.down("#btn-privacy-scroll");
            mouse.up("#btn-privacy-scroll");
        })
    }
}

function submitForm() {
    casper.evaluate(function () {
        document.querySelector('input[name="signup[privacy]"]').click()
        document.querySelector('input[name="signup[tos]"]').click()
        document.querySelector('#signup_submit').click()
    })
}

// 获取图片验证码
function captureCaptcha() {
    capture('图片验证码')
    casper.captureSelector('./capture/capcha.png', '.captcha__img');
}

// 校验验证码
function verifyCaptcha() {
    var child = spawn("php", ["./verify_code.php"])
    child.stdout.on("data", function (data) {
        if (data.indexOf('error') > -1) {
            casper.log('校验失败', 'info')
            phantom.exit()
        }

        casper.log('图片校验吗:' + data, 'info')

        // 填写验证码
        casper.evaluate(function (code) {
            document.querySelector('[name="signup[userpin]"]').value = code
            document.querySelector('.modal-footer .btn-primary').click()
        }, data)

        // 最后一步

    })
}