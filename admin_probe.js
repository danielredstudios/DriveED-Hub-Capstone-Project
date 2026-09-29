const { chromium } = require('playwright');
const BASE='http://localhost:8004';
const SCHOOL='drived-hub';
const USER={email:'admin1@driveedhub.test', password:'DriveDemo123'};
const modules=[
  {name:'Dashboard', path:'/'+SCHOOL+'/admin'},
  {name:'User Management', path:'/'+SCHOOL+'/admin/user-management'},
  {name:'Courses', path:'/'+SCHOOL+'/admin/courses'},
  {name:'Schedules', path:'/'+SCHOOL+'/admin/schedules'},
  {name:'Branches', path:'/'+SCHOOL+'/admin/branches'},
  {name:'Vehicles', path:'/'+SCHOOL+'/admin/vehicles'},
  {name:'Enrollments', path:'/'+SCHOOL+'/admin/enrollments'},
  {name:'Theoretical', path:'/'+SCHOOL+'/admin/theoretical'},
  {name:'Sessions', path:'/'+SCHOOL+'/admin/sessions'},
  {name:'Phase Progressions', path:'/'+SCHOOL+'/admin/phase-progressions'},
  {name:'Verify Session Completion', path:'/'+SCHOOL+'/admin/verify-session-completion'},
  {name:'Payments', path:'/'+SCHOOL+'/admin/payments'},
  {name:'Reports', path:'/'+SCHOOL+'/admin/reports'},
  {name:'Admin Management', path:'/'+SCHOOL+'/admin/admin-management'},
  {name:'Settings', path:'/'+SCHOOL+'/admin/settings'},
  {name:'Materials', path:'/'+SCHOOL+'/admin/materials'},
  {name:'Removal Requests', path:'/'+SCHOOL+'/admin/removal-requests'},
  {name:'Profile', path:'/'+SCHOOL+'/admin/profile'},
];
(async()=>{
  const browser=await chromium.launch({headless:true});
  const ctx=await browser.newContext();
  const page=await ctx.newPage();
  await page.goto(BASE+'/'+SCHOOL+'/login',{waitUntil:'domcontentloaded'});
  await page.getByLabel(/email/i).first().fill(USER.email);
  await page.getByLabel(/password/i).first().fill(USER.password);
  await page.getByRole('button',{name:/log in|sign in|login/i}).click();
  await page.waitForLoadState('networkidle',{timeout:15000}).catch(()=>{});
  console.log('LOGGED IN AS '+USER.email+' -> '+page.url());
  for(const m of modules){
    await page.goto(BASE+m.path,{waitUntil:'domcontentloaded'});
    await page.waitForLoadState('networkidle',{timeout:10000}).catch(()=>{});
    await page.waitForTimeout(1500);
    const title=await page.title().catch(()=> '');
    const heading=await page.locator('h1,h2,.page-title').first().textContent().catch(()=> '');
    const stats=await page.locator('.stat-card, .card').count().catch(()=>0);
    const hasTable=await page.locator('table').count();
    const rows=await page.locator('table tbody tr').count().catch(()=>0);
    const bodySnippet=(await page.content()).substring(0,800).replace(/\s+/g,' ').substring(0,400);
    console.log('['+m.name+'] url='+page.url()+' | title='+JSON.stringify(title)+' | heading='+JSON.stringify((heading||'').trim().substring(0,80))+' | cards='+stats+' table='+hasTable+' rows='+rows);
    await page.screenshot({path:'C:/Users/RED/AppData/Local/Temp/opencode/admin_'+m.name.replace(/[^a-zA-Z0-9]/g,'_')+'.png', fullPage:true}).catch((e)=>console.log('ss fail',e.message));
  }
  // also check system admin
  const p2=await ctx.newPage();
  await p2.goto(BASE+'/system-admin/login',{waitUntil:'domcontentloaded'});
  await p2.waitForTimeout(1000);
  console.log('[System-Admin Login] title='+JSON.stringify(await p2.title()));
  await p2.screenshot({path:'C:/Users/RED/AppData/Local/Temp/opencode/admin_SystemAdminLogin.png', fullPage:true}).catch(()=>{});
  await browser.close();
})();
