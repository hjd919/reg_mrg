var utils = require('utils')

Array.prototype.contains = function (obj) {
    var i = this.length;
    while (i--) {
        if (this[i] === obj) {
            return true;
        }
    }
    return false;
}

// 获取我们自己的关键词 [{id,keyword,keyword_id}]
var getOurKeywords = function (keywordData, keyword_data) {
    var our_keywords = keywordData.filter(function (row, index) {
        return keyword_data.contains(row[0])
    })

    return our_keywords
};

// 获取自己关键词的链接
var getOurKeywordsLinks = function (appid, keyword_ids) {
    var end_time = parseInt((new Date()).getTime() / 1000)
    var start_time = end_time - 86400
    var keyword_id
    var links = keyword_ids.map(function (row) {
        keyword_id = row[5]
        return 'https://www.chandashi.com/keyword/coverhistorynew/country/cn/appId/' + appid + '/keyword/' + keyword_id + '.html?start=' + start_time + '&end=' + end_time + '&switchtype=hour'
    })
    return links
}

var casper = require('casper').create({
    // viewportSize: { width: 1024, height: 768 },
    pageSettings: {
        loadImages: false,        // The WebPage instance used by Casper will
        loadPlugins: false         // use these settings
    },
    // logLevel: "info",
    // verbose: true,
    onWaitTimeout: function (timeout) {
        this.echo('error:onWaitTimeout' + timeout)
    }
});

var step = 1;

// 获取命令行的输入参数
var cli = casper.cli
// appid
var appid = cli.get('appid')
if (!appid) {
    casper.echo('缺少appid capserjs chandashi.js --appid={appid}').exit();
}
var app_ids = cli.get('app_ids')
if (!app_ids) {
    casper.echo('缺少app_ids capserjs chandashi.js --appid={appid} --app_ids').exit();
}
app_ids = JSON.parse(app_ids)
// var app_ids = [{ id: 3, keyword: '聊天软件' }]

var keyword_list_url = 'https://www.chandashi.com/apps/keywordcover/appId/' + appid + '/country/cn.html'

// casper.on("remote.message", function (msg) {
//     this.echo("remote.message: " + msg);
// });

// 获取截图名称
var get_pic_name = function (step) {
    return './casperjs/capture/step_' + step + '.jpg'
}

casper.start(keyword_list_url, function () {

    // 优化：开始就不登录了
    if (!this.exists('.keyword-panel-top')) {

        // # 点击登录
        this.click('button.user-login')

        // # 等待登录界面
        this.waitForSelector('form[id="logForm"]', function () {

            // # 填写登录信息
            this.fill('form[id="logForm"]', {
                password: "hsm123456",
                username: "15510658226",
            }, true)

            // // 截图
            // this.capture(get_pic_name(step));
            // step++
        })
            .waitForSelector("div[class='keyword-panel-top']")
    }
});

var our_keywords_links
var keyword_ranks = []

casper.then(function () {

    // 执行浏览器脚本
    var keywordData = this.evaluate(function () {
        // 获取页面的关键词数据
        return keywordData
    });

    if (!keywordData) {
        this.echo('error:no keywords').exit()
    }

    // 获取所需关键词的历史趋势
    // this.echo(keywordData)

    // 格式化app_ids => keyword数据
    var keyword_data = app_ids.map(function (val) {
        return val.keyword
    })
    // 格式化关键词对应app_id {keyword:app_id}
    var len = app_ids.length, keyword_ids = {}
    for (var index = 0; index < len; index++) {
        keyword_ids[app_ids[index].keyword] = app_ids[index].id;
    }

    // 过滤出所需关键词
    var our_keywords = getOurKeywords(keywordData, keyword_data)

    // 获取所需关键词的历史趋势链接
    our_keywords_links = getOurKeywordsLinks(appid, our_keywords)

    // 获取关键词的历史趋势
    var keyword_rank_page,
        keyword_rank,
        keyword_rank_data,
        keyword,
        len,
        point,
        pont_time,
        rank,
        min_rank,
        min_rank_start,
        on_rank_time,
        index,
        rank_data,
        min_rank_end
    this.each(our_keywords_links, function (self, link) {

        self.thenOpen(link, {
            method: 'get',
            headers: {
                'Accept': 'application/json'
            }
        }, function (response) {
            // 解析返回的数据 获取关键词趋势数据
            keyword_rank_page = this.getPageContent()

            keyword_rank_page = JSON.parse(keyword_rank_page)

            if (!keyword_rank_page.data) {
                this.echo('error:history_rank_error' + this.getPageContent()).exit()
            }
            
            keyword_rank_page = keyword_rank_page.data
            keyword_rank_page = keyword_rank_page.points[0]
            keyword_rank_data = keyword_rank_page.data

            keyword = keyword_rank_page.name

            // 分析出在榜时长、上榜开始、结束、现排名
            // 找出最小的排名
            len = keyword_rank_data.length
            min_rank = 10000
            min_rank_start = 0
            on_rank_time = 0
            min_rank_end = 0
            for (index = 0; index < len; index++) {
                point = keyword_rank_data[index];
                point_time = point[0]
                rank = point[1]

                if (rank > min_rank) {
                    continue
                }

                // 如果有更小的排名
                if (rank < min_rank) {
                    min_rank = rank
                    min_rank_start = point_time / 1000
                    on_rank_time = 0
                }

                // 结束时间为下一个小时
                min_rank_end = keyword_rank_data[index][0] / 1000

                // 在榜时长+1
                on_rank_time++
            }

            keyword_rank = {
                'keyword': keyword,
                'rank_data': keyword_rank_data,
                'after_rank': min_rank,
                'on_rank_time': on_rank_time,
                'on_rank_start': min_rank_start,
                'on_rank_end': min_rank_end,
                'id': keyword_ids[keyword],
            }

            keyword_ranks.push(keyword_rank)
        });
    });
})

casper.run(function () {
    // { [id, after_rank, on_rank_time, on_rank_start, on_rank_end,ranks] }
    this.echo(JSON.stringify(keyword_ranks));
    this.exit();
});