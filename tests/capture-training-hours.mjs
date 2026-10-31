import puppeteer from 'puppeteer-core';
import fs from 'node:fs/promises';
import {execFileSync} from 'node:child_process';
import {fileURLToPath} from 'node:url';
import assert from 'node:assert/strict';
const fixture=mode=>execFileSync('docker',['exec','driveed_hub_app','php','tests/oct31-hours-fixture.php',mode],{encoding:'utf8'});
let browser;
try {
 const data=JSON.parse(fixture('prepare'));
 browser=await puppeteer.launch({executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe',headless:true,args:['--no-sandbox'],defaultViewport:{width:1440,height:1000}});
 const page=await browser.newPage();
 await page.goto('http://localhost:8004/drived-hub/login',{waitUntil:'networkidle2'});
 await page.type('[name="email"]','instructor1@driveedhub.test');
 await page.type('[name="password"]','DriveDemo123');
 await Promise.all([page.waitForNavigation({waitUntil:'networkidle2'}),page.click('button[type="submit"]')]);
 await page.goto('http://localhost:8004/drived-hub/instructor/sessions/create?enrollment_id='+data.enrollment_id,{waitUntil:'networkidle2'});
 assert(await page.$eval('#trainingHoursNotice',el=>el.textContent.includes('already completed')));
 assert(await page.$eval('#sessionForm button[type="submit"]',el=>el.disabled));
 const result=await page.evaluate(async enrollmentId=>{
  const form=document.getElementById('sessionForm');
  const response=await fetch(form.action,{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},body:JSON.stringify({enrollment_id:enrollmentId,session_type:'theoretical',hours_completed:0.5,session_date:new Date().toLocaleDateString('en-CA'),session_time:'01:00'})});
  return {status:response.status,body:await response.json()};
 },data.enrollment_id);
 assert.equal(result.status,422);
 assert(result.body.errors.hours_completed.some(message=>message.includes('already completed')));
 const out=new URL('../docs/screenshots/oct31-findings/',import.meta.url);
 await fs.writeFile(new URL('training-hours-result.json',out),JSON.stringify(result,null,2));
 await page.screenshot({path:fileURLToPath(new URL('training-hours-completed.png',out)),fullPage:true});
 console.log('PASS: Completed training hours show an explanation and disable submission; a tampered POST is rejected with HTTP 422.');
} finally {
 if(browser)await browser.close();
 console.log(fixture('cleanup'));
}
