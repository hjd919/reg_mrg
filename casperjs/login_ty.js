// var utils = require('utils')
var process = require("child_process")
var execFile = process.execFile
var spawn = process.spawn
var fs = require('fs');
// fs.removeTree('./capture');


var casper = require('casper').create({
    // viewportSize: { width: 1024, height: 768 },
    pageSettings: {
        userAgent: 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/64.0.3282.186 Safari/537.36',
        // resourceTimeout: 6000,
        loadImages: false,        // The WebPage instance used by Casper will
        loadPlugins: false         // use these settings
    },
    // logLevel: "info",
    // verbose: true,
});
// casper.on("remote.message", function (msg) {
//     this.echo("=====remote.message:======== " + msg);
// });
phantom.outputEncoding = "gbk2312"

casper.options.onResourceRequested = function (C, requestData) {
    // console.log('Request (#' + JSON.stringify(requestData.url) + '): ' + "\n");
    // if ((/https?:\/\/.+?\.css/gi).test(requestData['url']) || requestData['Content-Type'] == 'text/css') {
};

// 输入
var cli = casper.cli
var email_name = cli.get('email_name')
if (!email_name) {
    casper.echo('缺少email_name capserjs chandashi.js --password={password} --email_name={email_name}').exit();
}

// console.log("\n" + '注册' + email_name)

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
casper.start('http://mail.tianya.cn/home/hn/index.jsp', function () {
    this.evaluate(function () {
        document.body.bgColor = 'white';
    });
    // casper.waitForSelector('div[data-field-name="email"]', function () {

    // # 填写登录信息
    casper.evaluate(function (email_name) {
        // 密码
        document.querySelector('input[name="username"]').value = email_name
        document.querySelector('select[name="hostname"]').value = 'hainan.net'
        document.querySelector('input[name="password"]').value = email_name
        document.querySelector('.imagesmargin').click()
    }, email_name)
    step = capture(casper, step)
});

var topage = function () {
    casper.wait(500, function () {
        if (this.exists('#psd_question')) {
            // 邮件实名页面
            casper.evaluate(function () {
                document.querySelector('select[name="psd_question"]').value = '您的父亲名字是？'
                document.querySelector('input[name="psd_answer"]').value = '赵三'
                document.querySelector('input[name="pwd_ok_btn"]').click()
            })
            casper.waitForResource(function testResource(resource) {
                return resource.url.indexOf('hainanMibaoResult.jsp') > 0;
            }, function onReceived() {
                casper.evaluate(function () {
                    document.querySelector('.submit-btn').click()
                })
            })
            topage();
        } else {
            // this.echo(this.getPageContent());
            // 在首页
            casper.evaluate(function () {
                window.parent.frames.qP.document.querySelector('#aMenuSpam').click()
            })
        }
    });
}
topage();

casper.wait(500, function () {
    // 截图
    step = capture(casper, step)

    casper.evaluate(function () {
        // 只取第一封信
        var offset = window.parent.frames.qP.document.querySelectorAll('#dvLetterList').length
        window.parent.frames.qP.document.querySelectorAll('#dvLetterList .clist3 > a')[offset - 1].click()
    })
});
casper.wait(500, function () {
    // 截图
    step = capture(casper, step)

    var checkcode = casper.evaluate(function () {
        return window.parent.frames.qP.part1.document.querySelector('#paragraphs .verification-code').innerHTML
    })
    console.log(checkcode)
});

casper.run();