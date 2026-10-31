import puppeteer from 'puppeteer-core';
import assert from 'node:assert/strict';
import fs from 'node:fs/promises';
const out='docs/screenshots/inactive-roster-fix';await fs.mkdir(out,{recursive:true});
const browser=await puppeteer.launch({executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe',headless:true,args:['--no-sandbox'],defaultViewport:{width:1680,height:1100}});
const errors=[];const results=[];
try {
const page=await browser.newPage();page.on('pageerror',e=>errors.push(e.message));
const base='http://localhost:8004/qa-inactive-roster';
await page.goto(base+'/login',{waitUntil:'networkidle2'});await page.type('[name=email]','qa-inactive-admin@example.invalid');await page.type('[name=password]','QA-Local-Only-123!');await Promise.all([page.waitForNavigation({waitUntil:'networkidle2'}),page.click('button[type=submit]')]);
await page.goto(base+'/admin/user-management',{waitUntil:'networkidle2'});
assert.equal(await page.$eval('.stat-card.inactive .stat-value',el=>el.textContent.trim()),'5');
assert.match(await page.$eval('.stat-card.inactive .stat-detail',el=>el.textContent.replace(/\s+/g,' ')),/2 Students.*3 Instructors/);
await page.click('.stat-card.inactive');await page.waitForFunction(()=>document.querySelectorAll('#usersTable tbody tr').length===5);
await page.waitForFunction(()=>getComputedStyle(document.getElementById('loadingOverlay')).display==='none');
let users=await page.$$eval('#usersTable tbody tr',els=>els.map(el=>({name:el.querySelector('td').innerText,status:el.dataset.status,role:el.dataset.role})));
assert.equal(users.filter(u=>u.role==='student').length,2);assert.equal(users.filter(u=>u.role==='instructor').length,3);assert.equal(users.filter(u=>u.status==='archived').length,4);assert.equal(users.filter(u=>u.status==='inactive').length,1);
await page.screenshot({path:out+'/inactive-card-five-users.png',fullPage:true});results.push('Inactive card click displays all 5 users: 2 archived students, 2 archived instructors, 1 deactivated instructor.');
for(const [query,count,label] of [['status=inactive&role=student',2,'students'],['status=inactive&role=instructor',3,'instructors'],['status=inactive&search=Instructor%203',1,'deactivated-instructor'],['status=active',2,'active-users'],['filtered=1',7,'all-users']]){
const r=await page.goto(base+'/admin/user-management?'+query,{waitUntil:'networkidle2'});assert.equal(r.status(),200);assert.equal(await page.$$eval('#usersTable tbody tr',els=>els.length),count);await page.screenshot({path:out+'/'+label+'.png',fullPage:true});results.push(label+': '+count+' correct users');}
assert.equal(errors.length,0,errors.join('\n'));await fs.writeFile(out+'/results.json',JSON.stringify({results,errors,users},null,2));console.log('PASS: '+results.join('\nPASS: '));
}finally{await browser.close()}
