import puppeteer from 'puppeteer-core';
import fs from 'node:fs/promises';
import assert from 'node:assert/strict';
await fs.mkdir('docs/screenshots/home-branding',{recursive:true});
const browser=await puppeteer.launch({executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe',headless:true,args:['--no-sandbox'],defaultViewport:{width:1706,height:810}});
try{const page=await browser.newPage();const response=await page.goto('http://localhost:8004/',{waitUntil:'networkidle2'});assert.equal(response.status(),200);assert.equal(await page.title(),'DriveED Hub');assert.match(await page.$eval('body',el=>el.innerText),/DriveED Hub\. All rights reserved\./);await page.screenshot({path:'docs/screenshots/home-branding/driveed-hub-home.png',fullPage:true});console.log('PASS: Home title and copyright use DriveED Hub.');}finally{await browser.close()}
