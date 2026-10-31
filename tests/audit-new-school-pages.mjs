import puppeteer from 'puppeteer-core';
import fs from 'node:fs/promises';
import {execFileSync} from 'node:child_process';
const out='docs/screenshots/new-school-audit';await fs.mkdir(out,{recursive:true});
const routes=JSON.parse(execFileSync('docker',['exec','driveed_hub_app','php','artisan','route:list','--path=admin','--json'],{encoding:'utf8'})).filter(r=>r.method.includes('GET')&&r.uri.startsWith('{school}/admin')&&!r.uri.replace('{school}','').includes('{')&&!r.uri.includes('/export')&&!r.uri.includes('/stats'));
const browser=await puppeteer.launch({executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe',headless:true,args:['--no-sandbox'],defaultViewport:{width:1440,height:1000}});const results=[];
try{const page=await browser.newPage();const base='http://localhost:8004/qa-new-school-user-management';await page.goto(base+'/login');await page.type('[name=email]','qa-new-school@example.invalid');await page.type('[name=password]','QA-Local-Only-123!');await Promise.all([page.waitForNavigation({waitUntil:'networkidle2'}),page.click('button[type=submit]')]);
for(const route of routes){const errors=[];const handler=e=>errors.push(e.message);page.on('pageerror',handler);const networkHandler=r=>{if(r.status()>=500&&r.url().startsWith('http://localhost:8004/'))errors.push('HTTP '+r.status()+' '+r.url());};page.on('response',networkHandler);let result={route:route.name};try{const r=await page.goto('http://localhost:8004/'+route.uri.replace('{school}','qa-new-school-user-management'),{waitUntil:'networkidle2'});result.status=r.status();result.url=page.url();result.title=await page.title();result.errors=errors;
if(route.name==='schools.admin.payments.index'){
if(!await page.$('.sidebar'))throw Error('Payments must retain admin navigation');
const count=await page.$$eval('.empty-state',els=>els.filter(el=>el.getBoundingClientRect().height>0).length);if(count!==1)throw Error('Expected one visible empty payment state, found '+count);
await page.setViewport({width:390,height:844,isMobile:true,hasTouch:true});await page.screenshot({path:out+'/payments-mobile.png',fullPage:false});
const mobileCount=await page.$$eval('.empty-state',els=>els.filter(el=>el.getBoundingClientRect().height>0).length);if(mobileCount!==1)throw Error('Expected one mobile empty payment state');
await page.setViewport({width:1440,height:1000});
}
await page.screenshot({path:out+'/'+route.name.replace('schools.admin.','')+'.png',fullPage:false});
result.ajax=await page.evaluate(async url=>{const r=await fetch(url,{headers:{'X-Requested-With':'XMLHttpRequest','Accept':'text/html'}});return {status:r.status,text:(await r.text()).slice(0,80)}},page.url());
const actions={'schools.admin.schedules':()=>window.openCreateModal(),'schools.admin.branches.index':()=>window.openBranchModal(),'schools.admin.courses':()=>window.openCreateModal(),'schools.admin.vehicles.index':()=>window.openVehicleModal('createVehicleModal'),'schools.admin.admin-management.index':()=>window.openCreateModal('branch_secretary'),'schools.admin.userManagement':()=>window.openCreateInstructorModal()};
if(actions[route.name]){await page.evaluate(actions[route.name]);await page.screenshot({path:out+'/'+route.name.replace('schools.admin.','')+'-create-dialog.png',fullPage:false});}
result.excerpt=await page.$eval('body',e=>e.innerText.slice(0,350));}catch(e){result.error=e.message}page.off('pageerror',handler);page.off('response',networkHandler);results.push(result);console.log(JSON.stringify(result));}
const exports=JSON.parse(execFileSync('docker',['exec','driveed_hub_app','php','artisan','route:list','--path=admin','--json'],{encoding:'utf8'})).filter(r=>r.method.includes('GET')&&r.uri.startsWith('{school}/admin')&&!r.uri.replace('{school}','').includes('{')&&(r.uri.includes('/export')||r.uri.includes('/stats')));
for(const route of exports){const result=await page.evaluate(async url=>{const r=await fetch(url);return {status:r.status,type:r.headers.get('content-type'),length:(await r.arrayBuffer()).byteLength}},'http://localhost:8004/'+route.uri.replace('{school}','qa-new-school-user-management'));if(result.status!==200)throw Error(route.name+JSON.stringify(result));console.log('PASS export/API '+route.name);}
await fs.writeFile(out+'/results.json',JSON.stringify(results,null,2));
const failures=results.filter(r=>r.error||r.status!==200||r.errors.length||r.ajax?.status!==200);if(failures.length)throw Error(JSON.stringify(failures));console.log('PASS: '+results.length+' routes, direct and AJAX loads, plus six create dialogs.');}finally{await browser.close()}
