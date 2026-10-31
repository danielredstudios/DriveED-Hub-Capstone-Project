import puppeteer from 'puppeteer-core';
import fs from 'node:fs/promises';
import assert from 'node:assert/strict';
const out='docs/screenshots/hours-login-fix';await fs.mkdir(out,{recursive:true});
const browser=await puppeteer.launch({executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe',headless:true,args:['--no-sandbox']});
const page=await browser.newPage();const browserErrors=[];page.on('pageerror',e=>browserErrors.push(e.message));page.on('response',r=>{if(r.status()>=500&&r.url().startsWith('http://localhost:8004/'))browserErrors.push('HTTP '+r.status()+' '+r.url());});const base='http://localhost:8004/drived-hub';let courseId;
try {
 for(const [width,height] of [[320,568],[390,844],[768,1024],[1440,900],[667,375]]){
 await page.setViewport({width,height,isMobile:width<768,hasTouch:width<768});await page.goto(base+'/login',{waitUntil:'networkidle2'});
 const sizes=await page.evaluate(()=>({viewport:innerWidth,overflow:document.documentElement.scrollWidth>innerWidth,card:document.querySelector('.login-container').getBoundingClientRect().width,font:parseFloat(getComputedStyle(document.querySelector('[name=email]')).fontSize)}));
 assert.equal(sizes.viewport,width);assert.equal(sizes.overflow,false);if(width<=480){assert(sizes.card>=width-40);assert(sizes.font>=16)}
 await page.screenshot({path:`${out}/login-${width}.png`,fullPage:true});console.log('PASS login',width,height,sizes);
 }
 await page.setViewport({width:1440,height:1000});await page.goto(base+'/login');await page.type('[name=email]','admin1@driveedhub.test');await page.type('[name=password]','DriveDemo123');await Promise.all([page.waitForNavigation({waitUntil:'networkidle2'}),page.click('button[type=submit]')]);await page.goto(base+'/admin/courses',{waitUntil:'networkidle2'});
 const post=async(url,data)=>page.evaluate(async({url,data})=>{const r=await fetch(url,{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},body:JSON.stringify(data)});return {status:r.status,text:await r.text()}},{url,data});
 let r=await post(base+'/admin/courses',{title:'Hours persistence verification',type:'standard',course_type:'combo',license_type:'non_professional',hours_required:23.5,price:0,status:'active'});assert(r.status<400,r.text);
 await page.reload({waitUntil:'networkidle2'});courseId=await page.evaluate(()=>coursesData.find(c=>c.title==='Hours persistence verification').id);
 r=await post(`${base}/admin/courses/${courseId}/packages`,{name:'Hours regression package',transmission_type:'automatic',price:13000,tdc_hours:15,pdc_hours:8.5});assert(r.status<400,r.text);await page.reload({waitUntil:'networkidle2'});
 let saved=await page.evaluate(id=>coursesData.find(c=>c.id===id),courseId);assert.equal(Number(saved.hours_required),23.5);let pkg=saved.packages[0];assert.equal(Number(pkg.tdc_hours),15);assert.equal(Number(pkg.pdc_hours),8.5);assert.equal(Number(pkg.training_hours),23.5);
 await page.evaluate(id=>openEditModal(id),courseId);assert.equal(await page.$eval('#courseHoursRequired',el=>el.value),'23.50');await page.$eval('#courseHoursRequired',el=>el.scrollIntoView({block:'center'}));await page.screenshot({path:out+'/course-hours.png',fullPage:false});await page.evaluate(()=>closeCourseModal());
 await page.evaluate(({courseId,pkg})=>openPackageModal(courseId,pkg.id),{courseId,pkg});await page.screenshot({path:out+'/package-created.png',fullPage:false});
 r=await post(`${base}/admin/courses/${courseId}/packages/${pkg.id}`,{_method:'PUT',name:pkg.name,transmission_type:'automatic',price:13000,tdc_hours:0,pdc_hours:10});assert(r.status<400,r.text);await page.reload({waitUntil:'networkidle2'});
 saved=await page.evaluate(id=>coursesData.find(c=>c.id===id),courseId);pkg=saved.packages[0];assert.equal(Number(pkg.tdc_hours),0);assert.equal(Number(pkg.pdc_hours),10);assert.equal(Number(pkg.training_hours),10);
 await page.evaluate(({courseId,pkg})=>openPackageModal(courseId,pkg.id),{courseId,pkg});await page.screenshot({path:out+'/package-updated.png',fullPage:false});
 r=await post(`${base}/admin/courses/${courseId}/packages/${pkg.id}`,{_method:'PUT',name:pkg.name,transmission_type:'automatic',price:13000,tdc_hours:-1,pdc_hours:10});assert.equal(r.status,422);console.log('PASS course hours, package create/update/zero/fractional totals and negative-hour rejection');
assert.equal(browserErrors.length,0,browserErrors.join('\n'));
}finally{if(courseId)await page.evaluate(async({base,courseId})=>fetch(`${base}/admin/courses/${courseId}`,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},body:JSON.stringify({_method:'DELETE'})}),{base,courseId});await browser.close();}
