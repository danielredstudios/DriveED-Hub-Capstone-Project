import puppeteer from 'puppeteer-core';
import assert from 'node:assert/strict';
import fs from 'node:fs/promises';
const output='docs/screenshots/new-school-user-management';await fs.mkdir(output,{recursive:true});
const browser=await puppeteer.launch({executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe',headless:true,args:['--no-sandbox'],defaultViewport:{width:1440,height:1000}});
try {
const page=await browser.newPage();const errors=[];page.on('pageerror',e=>errors.push(e.message));
const base='http://localhost:8004/qa-new-school-user-management';
await page.goto(base+'/login',{waitUntil:'networkidle2'});await page.type('[name=email]','qa-new-school@example.invalid');await page.type('[name=password]','QA-Local-Only-123!');await Promise.all([page.waitForNavigation({waitUntil:'networkidle2'}),page.click('button[type=submit]')]);
const r=await page.goto(base+'/admin/user-management',{waitUntil:'networkidle2'});assert.equal(r.status(),200);assert((await page.title()).length>0);
const links=await page.$$eval('a',els=>els.filter(e=>e.textContent.trim()==='create a course').map(e=>e.href));assert.equal(links.length,2);assert(links.every(h=>h===base+'/admin/courses'));
await page.screenshot({path:output+'/user-management-loaded.png',fullPage:false});
await page.evaluate(()=>openCreateStudentModal());await page.screenshot({path:output+'/no-courses-student-modal.png',fullPage:false});
const result=await page.goto(links[0],{waitUntil:'networkidle2'});assert.equal(result.status(),200);assert.equal(page.url(),base+'/admin/courses');assert.equal(errors.length,0,errors.join('\n'));
await page.screenshot({path:output+'/create-course-link-works.png',fullPage:false});
console.log('PASS: New school with zero courses: User Management HTTP 200, both empty-state links use the correct school route, student modal opens, course link HTTP 200, no browser errors.');
const existing='http://localhost:8004/drived-hub';await page.goto(existing+'/login',{waitUntil:'networkidle2'});await page.type('[name=email]','admin1@driveedhub.test');await page.type('[name=password]','DriveDemo123');await Promise.all([page.waitForNavigation({waitUntil:'networkidle2'}),page.click('button[type=submit]')]);const existingResponse=await page.goto(existing+'/admin/user-management',{waitUntil:'networkidle2'});assert.equal(existingResponse.status(),200);console.log('PASS: Existing school User Management HTTP 200.');
}finally{await browser.close();}
