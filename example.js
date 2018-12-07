const puppeteer = require('puppeteer');

(async () => {
    // const browser = await puppeteer.launch();
    const browser = await puppeteer.launch({
        headless: false,
        slowMo: 250,
        devtools: true
    });
    const page = await browser.newPage();
    await page.goto('https://example.com');

    await page.waitForSelector('#userName', {
        timeout: 2000,
    });
    await page.type('#userName', 'mockuser');
    await page.type('#password', 'wrong_password');
    await page.click('button[type="submit"]');
    await page.waitForSelector('.ant-alert-error'); // should display error

    console.log('Dimensions:', dimensions);
    await browser.close();
})();
