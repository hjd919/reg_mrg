var casper = require('casper').create({
    // viewportSize: { width: 1024, height: 768 },
    pageSettings: {
        loadImages: false,        // The WebPage instance used by Casper will
        loadPlugins: false         // use these settings
    },
    // logLevel: "info",
    // verbose: true,
});

var step = 1;

// 获取命令行的输入参数
var cli = casper.cli
// appid
var appid = cli.get('appid')
if(!appid){
    casper.echo('缺少appid capserjs chandashi.js --appid={appid}').exit();
}
// casper.on('http.status.404', function (resource) {
//     this.log('Hey, this one is 404: ' + resource.url, 'ERROR');
// });
// casper.on("page.error", function (msg, trace) {
//     this.echo("Error: " + msg, "ERROR");
// });
// casper.on("remote.message", function (msg) {
//     this.echo("remote.message: " + msg);
// });

// 获取截图名称
var get_pic_name = function (step) {
    return './capture/step_' + step + '.jpg'
}

/* // 优化：开始就不登录了
this.exists('#my_super_id') */

casper.start('https://www.chandashi.com/user/login.html', function () {

    // 填写登录信息
    this.fill('form[id="logForm"]', {
        password: "hsm123456",
        username: "15510658226",
    }, true)

    // 截图
    this.capture(get_pic_name(step));
    step++

}); // 打开网页

// 等待登录完成
casper.waitForSelector(".phone-title", function(){
    // 截图
    this.capture(get_pic_name(step));
    step++
    // 保存登录cookie http://www.voidcn.com/article/p-slcwbels-bc.html
});

// 进入关键词列表
casper.thenOpen('https://www.chandashi.com/apps/keywordcover/appId/' + appid + '/country/cn.html', function () {


    // 执行浏览器脚本
    var keywordData = this.evaluate(function () {
        // 获取页面的关键词数据
        return keywordData
    });

    // 获取关键词

    this.echo(keywordData)

    this.open('https://www.chandashi.com/keyword/coverhistorynew/country/cn/appId/' + appid + '/keyword/875074.html?start=1513267200&end=1513853644&switchtype=day', {
        method: 'get',
        headers: {
            'Accept': 'application/json'
        }
    });
})

casper.run(function () {
    require('utils').dump(JSON.parse(this.getPageContent()));
    this.exit();
});