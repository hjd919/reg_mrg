const puppeteer = require('puppeteer');

(async () => {
    const browser = await puppeteer.launch({
        ignoreHTTPSErrors: true,
        headless: false,
        args: [
            "--proxy-server=127.0.0.1:8888",
            "--no-sandbox",
            "--disable-setuid-sandbox"
        ]
    });
    const page = await browser.newPage();

    page.setUserAgent("Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.111 Safari/537.36");

    // await page.authenticate({ 'username': 'cn_xs', 'password': 'did=did&uid=82b29f87735d7bc675340cf71f9ba462&pid=-1&cid=-1&t=1544778703&sign=9ffe9290ab5ec023ce5e9f0acbdb17eb' })
    await page.goto('http://www.ip138.com/', { waitUntil: 'networkidle2' });
    // await page.pdf({ path: 'hn.pdf', format: 'A4' });

    // await browser.close();
})();