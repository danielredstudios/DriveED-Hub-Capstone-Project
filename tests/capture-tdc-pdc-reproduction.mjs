import puppeteer from 'puppeteer-core';
import fs from 'node:fs/promises';
import {execFileSync} from 'node:child_process';
import {fileURLToPath} from 'node:url';
import assert from 'node:assert/strict';

const out=new URL('../docs/screenshots/tdc-pdc-reproduction/',import.meta.url);
const fixture=mode=>{
 const output=execFileSync('docker',['exec','driveed_tdc_repro','php','tests/tdc-pdc-reproduction-fixture.php',mode],{encoding:'utf8'});
 if(mode==='cleanup')assert(output.startsWith('PASS:'),output);
 return output;
};
const state=JSON.parse(await fs.readFile(new URL('../tmp/tdc-pdc-reproduction-state.json',import.meta.url),'utf8'));
const base='http://localhost:8005/'+state.school_slug;
let browser;
try {
 browser=await puppeteer.launch({executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe',headless:true,args:['--no-sandbox'],defaultViewport:{width:1440,height:1080}});
 const page=await browser.newPage();
 await page.goto(base+'/login',{waitUntil:'networkidle2'});
 await page.type('[name="email"]',state.email);
 await page.type('[name="password"]',state.password);
 await Promise.all([page.waitForNavigation({waitUntil:'networkidle2'}),page.click('button[type="submit"]')]);
 console.log('Temporary login URL:',page.url());
 if(!page.url().endsWith('/student'))console.log((await page.$eval('body',el=>el.innerText)).slice(-1800));
 assert(page.url().endsWith('/student'),'Temporary student login must succeed.');
 const courseUrl=base+'/student/courses/'+state.pdc_course_id;
 await page.goto(courseUrl,{waitUntil:'networkidle2'});
 const submit=async()=>{
  await page.click('[onclick="openEnrollModal()"]');
  const packageId=await page.$eval('#enrollModal [name="package_id"]',el=>[...el.options].find(option=>option.text.includes('Manual'))?.value || [...el.options].find(option=>option.value)?.value);
  if(packageId)await page.select('#enrollModal [name="package_id"]',packageId);
  if(await page.$('#enrollModal [name="branch_id"]'))await page.select('#enrollModal [name="branch_id"]',String(state.branch_id));
  await page.select('#enrollModal [name="experience_level"]','new_driver');
  await Promise.all([page.waitForNavigation({waitUntil:'networkidle2'}),page.click('#enrollModal button[type="submit"]')]);
 };
 await submit();
 const error='You already have an active course in this school. Complete or cancel it before enrolling in another course.';
 await page.waitForFunction(message=>document.body.innerText.includes(message),{},error);
 await page.screenshot({path:fileURLToPath(new URL('before-blocked.png',out)),fullPage:false});
 const before=JSON.parse(fixture('before'));
 assert.equal(before.tdc_status,'approved');assert.equal(before.is_course_locked,true);assert.equal(before.pdc_id,null);
 console.log('PASS: Passed TDC student is blocked while the old TDC remains approved and locked.');

 const repair=JSON.parse(fixture('repair'));
 assert.equal(repair.tdc_status,'completed');assert.equal(repair.is_course_locked,false);assert.equal(repair.active_enrollment_id,null);
 await page.goto(courseUrl,{waitUntil:'networkidle2'});
 await submit();
 await page.waitForFunction(()=>document.body.innerText.includes('Enrollment request created!'));
 if(await page.$('#enrollmentPaymentModal [data-epm-close]'))await page.click('#enrollmentPaymentModal [data-epm-close]');
 await page.screenshot({path:fileURLToPath(new URL('after-submitted.png',out)),fullPage:false});
 const after=JSON.parse(fixture('after'));
 assert.equal(after.pdc_status,'pending');assert(after.pdc_id!==after.tdc_id);
 assert.equal(after.tdc_sessions,3);assert.equal(after.tdc_hours,15);assert.equal(after.training_hash_unchanged,true);
 console.log('PASS: Shared TDC passing flow completes and unlocks TDC; a separate PDC request is accepted with all three training logs preserved.');
 await fs.writeFile(new URL('browser-results.json',out),JSON.stringify({before_error:error,before,after,real_application_requests:true,payment_submitted:false,email_delivery_disabled:true},null,2));
} finally {
 if(browser)await browser.close();
 console.log(fixture('cleanup'));
}

const before=JSON.parse(await fs.readFile(new URL('before.json',out),'utf8'));
const after=JSON.parse(await fs.readFile(new URL('after.json',out),'utf8'));
const embedded=async name=>'data:image/png;base64,'+(await fs.readFile(new URL(name,out))).toString('base64');
const html=`<!doctype html><html lang="en"><head><meta charset="utf-8"><title>TDC to PDC before and after</title><style>*{box-sizing:border-box}body{margin:0;padding:28px;background:#f3f4f6;color:#172333;font:17px/1.6 Arial,sans-serif}h1{font-size:28px;margin:0 0 8px}h2{font-size:21px}p{margin:0 0 20px}main{display:grid;grid-template-columns:1fr 1fr;gap:24px}section{background:#fff;padding:20px;border:1px solid #cbd5e1}img{width:100%;height:auto}strong{display:block}footer{margin-top:20px;font-size:15px}</style></head><body><h1>Passed TDC student enrolling in PDC</h1><p>Safe reproduction using one temporary student and real browser submissions. The application code was not reverted.</p><main><section><h2>Before: enrollment blocked</h2><strong>TDC ${before.tdc_status}; course locked; active enrollment #${before.active_enrollment_id}</strong><p>Student role: student. TDC already passed. No PDC request created.</p><img src="${await embedded('before-blocked.png')}" alt="Actual local enrollment error"></section><section><h2>After: PDC application accepted</h2><strong>TDC ${after.tdc_status}; unlocked; active enrollment cleared</strong><p>Separate PDC request #${after.pdc_id}: ${after.pdc_status}, awaiting payment and approval.</p><img src="${await embedded('after-submitted.png')}" alt="Successful local PDC application"></section></main><footer>All 3 TDC session logs (15 hours) remained unchanged during the repair. Temporary student, enrollments, logs, and notifications were removed afterward. Hash comparisons confirmed all original records and Combo enrollments were unchanged. Outgoing email was disabled; no payment was submitted.</footer></body></html>`;
await fs.writeFile(new URL('comparison.html',out),html);
const renderer=await puppeteer.launch({executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe',headless:true,args:['--no-sandbox'],defaultViewport:{width:1800,height:1100}});
try {const page=await renderer.newPage();await page.setContent(html,{waitUntil:'load'});await page.screenshot({path:fileURLToPath(new URL('before-after-comparison.png',out)),fullPage:true});} finally {await renderer.close();}
console.log('Saved before, after, and side-by-side comparison screenshots.');
