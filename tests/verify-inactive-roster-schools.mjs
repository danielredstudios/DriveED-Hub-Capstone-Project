import puppeteer from 'puppeteer-core';
import assert from 'node:assert/strict';
import fs from 'node:fs/promises';
const out='docs/screenshots/inactive-roster-fix';await fs.mkdir(out,{recursive:true});
const browser=await puppeteer.launch({executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe',headless:true,args:['--no-sandbox'],defaultViewport:{width:1680,height:1100}});
try {
 for(const [slug,email,count,students,instructors] of [['qa-inactive-roster','qa-inactive-admin@example.invalid',4,2,2],['qa-inactive-roster-other','qa-other-admin@example.invalid',1,0,1]]){
 const context=await browser.createBrowserContext();const page=await context.newPage();const errors=[];page.on('pageerror',e=>errors.push(e.message));const base='http://localhost:8004/'+slug;
 await page.goto(base+'/login',{waitUntil:'networkidle2'});await page.type('[name=email]',email);await page.type('[name=password]','QA-Local-Only-123!');await Promise.all([page.waitForNavigation({waitUntil:'networkidle2'}),page.click('button[type=submit]')]);
 const response=await page.goto(base+'/admin/user-management?status=inactive&filtered=1',{waitUntil:'networkidle2'});assert.equal(response.status(),200);
 assert.equal(await page.$eval('.stat-card.inactive .stat-value',e=>Number(e.innerText)),count);
 assert.equal(await page.$$eval('#usersTable tbody tr',els=>els.length),count);
 const rows=await page.$$eval('#usersTable tbody tr',els=>els.map(e=>({role:e.dataset.role,status:e.dataset.status})));
 assert.equal(rows.filter(e=>e.role==='student').length,students);assert.equal(rows.filter(e=>e.role==='instructor').length,instructors);assert(rows.every(e=>e.status===(count===4?'archived':'inactive')));assert.equal(errors.length,0);
 await page.screenshot({path:out+'/'+slug+'-school-scoped.png',fullPage:true});console.log('PASS '+slug+': card and roster both show '+count+' users, with no cross-school leakage.');await context.close();
 }
}finally{await browser.close()}
