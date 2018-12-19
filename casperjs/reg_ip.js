var casper = require('casper').create({
    viewportSize: { width: 1920, height: 1080 },
    pageSettings: {
        userAgent: 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.84 Safari/537.36',
        // resourceTimeout: 6000,
        //loadImages: false,        // The WebPage instance used by Casper will
        // loadPlugins: false         // use these settings
    },
    // logLevel: "info",
    verbose: true,
    // onWaitTimeout: function (timeout) {
    //     this.echo('error:onWaitTimeout' + timeout)
    // }
});

var mouse = require("mouse").create(casper);

// 打印远端信息
casper.on("remote.message", function (msg) {
    this.echo("remote.message: " + msg);
});

// 截图
function capture(title) {
    var timestamp = (new Date()).valueOf();
    casper.capture('./capture/' + timestamp + title + '.jpg');
}

casper.start();
// casper.thenOpen('https://www.baidu.com', function () {
casper.thenOpen('http://www.ip138.com/', function () {
    this.evaluate(function () {
        document.body.bgColor = 'white';
    });
    capture('注册');

    // // 点击阅读协议
    // // clickProtocol(this)
    // this.mouse.click("#inx-lang-switch-button");
    // this.wait(1000)
    // capture('验证用户名2');
    return
    // 填写用户名
    fillUserName(casper)

    // 等待验证用户名
    casper.waitForSelector('#signup-alerts .alert-success', function () {
        this.echo('验证用户名成功')

        // 填写密码和姓名
        fillPassword(casper)

        // 点击阅读协议
        clickProtocol(casper)

        // this.echo('点击阅读协议')
        // casper.evaluate(function () {
        //     for (var index = 0; index < 13; index++) {
        //         document.querySelector('#btn-wrap-scroll').click()
        //     }
        // })
        // capture('点击阅读协议');
        // if (!this.exists('input[name="signup[privacy]"]')) {
        //     this.echo('协议按钮不存在')
        //     return false
        // }

    })
});
// casper.then(function () {
//     capture('ok');
// })

casper.run();

// 填写用户名
function fillUserName(casper) {
    casper.echo('填写用户名')
    casper.evaluate(function () {
        document.querySelector('input[name="signup[user]"]').value = 'hjd333hjd'
        document.querySelector('#check-uname').click()
    })
    casper.echo('验证用户名')
    capture('验证用户名');
}

// 填写密码和姓名
function fillPassword(casper) {
    casper.echo('填写密码和姓名')
    casper.evaluate(function () {
        document.querySelector('input[name="signup[forename]"]').value = 'hu'
        document.querySelector('input[name="signup[surname]"]').value = 'jianquan'
        document.querySelector('input[name="signup[password][password]"]').value = 'hjd825601'
        document.querySelector('input[name="signup[password][passwordRepeat]"]').value = 'hjd825601'
    })
}

// 点击协议
function clickProtocol(casper) {
    casper.mouse.click("#inx-lang-switch-button");
    casper.wait(500)
    capture('验证用户名2');
    // casper.echo('点击协议')
    // casper.mouse.click('#btn-wrap-scroll')
    // capture('点击协议1');
    // casper.mouse.click('#inx-lang-switch-button')
    // casper.wait(500)
    // capture('点击协议2');
    // casper.mouse.click('#inx-main-header-login')
    // casper.wait(500)
    // capture('点击协议3');
    // casper.mouse.move(".ifi-sign-question")
    // casper.wait(500)
    // capture('点击协议4');

    // casper.evaluate(function () {
    //     document.querySelector('input[name="signup[privacy]"]').click()
    //     document.querySelector('input[name="signup[tos]"]').click()
    //     document.querySelector('input[name="signup[submit]"]').click()
    // })
}