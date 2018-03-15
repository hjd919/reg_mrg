// casperjs --web-security=no ./reg_ru.js --cookies-file=./cookie.txt
// https://stackoverflow.com/questions/19199641/casperjs-download-csv-file
// https://www.icloud.com/

// 弹窗显示验证码不对
// 打码慢

// var utils = require('utils')
var process = require("child_process")
var execFile = process.execFile
var spawn = process.spawn
var fs = require('fs');
fs.removeTree('./capture');


var casper = require('casper').create({
    // viewportSize: { width: 1024, height: 768 },
    pageSettings: {
        userAgent: 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/64.0.3282.186 Safari/537.36',
        // resourceTimeout: 6000,
        loadImages: true,        // The WebPage instance used by Casper will
        loadPlugins: false         // use these settings
    },
    // logLevel: "info",
    // verbose: true,
    // waitTimeout: 2000,
    onWaitTimeout: function (timeout) {
        this.echo('error:onWaitTimeout' + timeout)
    }
});
casper.on("remote.message", function (msg) {
    this.echo("=====remote.message:======== " + msg);
});
casper.on("page.prompt", function (msg, value) {
    this.capture('screencap.png');
});
phantom.outputEncoding = "gbk2312"

// 输入
var cli = casper.cli
var email_name = cli.get('email_name')
if (!email_name) {
    casper.echo('缺少email_name capserjs chandashi.js --password={password} --email_name={email_name}').exit();
}
console.log("\n" + '注册' + email_name)

casper.options.onResourceRequested = function (C, requestData) {
    // console.log('Request (#' + JSON.stringify(requestData.url) + '): ' + "\n");
    // if ((/https?:\/\/.+?\.css/gi).test(requestData['url']) || requestData['Content-Type'] == 'text/css') {
};

// 获取截图名称
var step = 1;
var get_pic_name = function (step) {
    return './capture/step_' + step + '.jpg'
}
var capture = function (casper, step) {
    return step
    // 截图
    casper.capture(get_pic_name(step));
    step++
    return step
}

// 注册页面
casper.start('http://mail.hainan.net/webmailhainan/register.jsp', function () {
    this.evaluate(function () {
        document.body.bgColor = 'white';
    });
});

var img_path = './capture/capcha.png' // 验证码图片路径
var repeat_count = 0;
var reg_page = function () {
    console.log('repeat_count', repeat_count)
    if (repeat_count >= 5) {
        console.log('验证码失败')
        phantom.exit()
    }
    repeat_count++;
    casper.waitForResource(function testResource(resource) {
        return resource.url.indexOf("tianyaValidateCode.jsp") > 0;
    }, function onReceived() {
        step = capture(casper, step)

        //对指定元素截图 
        casper.captureSelector(img_path, '#codePic');
        // 获取验证码
        // casper.waitForExec("/usr/bin/php", ["-v"], function (response) {
        //     var data = response.data
        // }, function (timeout, response) {
        //     this.echo("Program finished by casper:" + '--timeout' + timeout + JSON.stringify(response.data));
        // })

        var child = spawn("php", ["verify_code.php"])
        child.stdout.on("data", function (data) {
            if (data.indexOf('error') >= 0 || !data) {
                casper.evaluate(function () {
                    document.querySelector('#change_code').click()
                })

                console.log('验证码获取失败了')
                return reg_page()
            }

            console.log('获取得到验证码了' + data)

            // 填写验证码
            casper.evaluate(function (code, email) {
                document.querySelector('[name="codetext"]').value = code

                // 其他信息
                document.querySelector('input[name="email_text"]').value = email
                document.querySelector('select[name="email_type"]').value = 'hainan.net'
                document.querySelector('input[name="pwd"]').value = email
                document.querySelector('input[name="pwd_again"]').value = email

                document.querySelector('#register_btn').click()
            }, data, email_name)

            step = capture(casper, step)

            // 密保页面            
            casper.wait(1000, function () {
                if (!this.exists('#psd_question')) {
                    // this.echo(this.getPageContent());
                    // 验证码不正确
                    console.log('验证码不正确,重新获取验证码')
                    casper.evaluate(function () {
                        document.querySelector('#change_code').click()
                    })

                    casper.wait(1000, function () {
                        step = capture(casper, step)
                        return reg_page()
                    })
                } else {
                    console.log('到达密保页面')

                    casper.evaluate(function () {
                        document.querySelector('select[name="psd_question"]').value = '您的父亲名字是？'
                        document.querySelector('input[name="psd_answer"]').value = '赵三'
                        document.querySelector('input[name="pwd_ok_btn"]').click()
                    })
                    step = capture(casper, step)
                }
            })

            // 到达找回密码流程页面
            casper.waitForResource(function testResource(resource) {
                return resource.url.indexOf('hainanMibaoResult.jsp') > 0;
            }, function onReceived() {
                // casper.wait(1000, function () {

                if (this.getTitle() == '找回密码流程') {
                    console.log('到达密保确认结果页面');
                } else {
                    console.log('没有到达密保确认结果页面');
                    this.echo(this.getPageContent());
                }
                casper.evaluate(function () {
                    document.querySelector('.submit-btn').click()
                })
                fs.write('./ok.txt', email_name + "\n", 'a+');
                step = capture(casper, step)
            })

            // 邮箱首页
            casper.waitForResource(function testResource(resource) {
                return resource.url.indexOf('webmailhainan/samedomain.js') > 0;
            }, function onReceived() {
                if (this.getTitle().indexOf(email_name) > 0) {
                    console.log('success reg' + "\n");
                } else {
                    this.echo(this.getPageContent());
                }
                // step = capture(casper, step)
                phantom.exit()
            })
        })

        console.log('等待图片验证码脚本执行完成。。。')
        casper.wait(12000)
    });
}
reg_page()

casper.run();