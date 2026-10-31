import {spawn} from 'node:child_process';
import fs from 'node:fs/promises';
const out='docs/screenshots/full-regression';await fs.mkdir(out,{recursive:true});
const results=[];
async function run(name,command,args){console.log('RUN '+name);const started=Date.now();let log='';const code=await new Promise((resolve,reject)=>{const child=spawn(command,args,{windowsHide:true});child.stdout.on('data',d=>log+=d);child.stderr.on('data',d=>log+=d);child.on('error',reject);child.on('close',resolve);});await fs.writeFile(out+'/'+name+'.log',log);results.push({name,code,seconds:Math.round((Date.now()-started)/1000)});console.log((code===0?'PASS ':'FAIL ')+name);if(code!==0)console.log(log.slice(-3000));return code===0;}
const php=(name,path,...args)=>run(name,'docker',['exec','driveed_hub_app','php',path,...args]);
const node=(name,path)=>run(name,process.execPath,[path]);
let schoolPrepared=false,statusPrepared=false;
try{
 await node('home-branding','tests/verify-home-branding.mjs');
 await node('hours-and-responsive-login','tests/verify-hours-login-fix.mjs');
 schoolPrepared=await php('prepare-new-school','tests/new-school-user-management-fixture.php');
 if(schoolPrepared){await node('new-school-pages-dialogs-exports','tests/audit-new-school-pages.mjs');await node('new-and-existing-user-management','tests/verify-new-school-user-management.mjs');}
 statusPrepared=await php('prepare-inactive-users','tests/inactive-roster-fixture.php');
 if(statusPrepared){await node('five-inactive-users','tests/verify-inactive-roster.mjs');if(await php('split-school-counts','tests/inactive-roster-fixture.php','split-schools'))await node('school-isolation','tests/verify-inactive-roster-schools.mjs');}
 for(const [name,path] of [['existing-bug-report','tests/verify-bug-report.php'],['existing-edge-cases','tests/verify-recheck-edge-cases.php'],['existing-oct31-findings','tests/verify-oct31-findings.php']])await php(name,path);
}finally{
 if(schoolPrepared)await php('cleanup-new-school','tests/new-school-user-management-fixture.php','cleanup');
 if(statusPrepared)await php('cleanup-inactive-users','tests/inactive-roster-fixture.php','cleanup');
 await fs.writeFile(out+'/results.json',JSON.stringify(results,null,2));
 console.log('RESULT '+results.filter(r=>r.code===0).length+'/'+results.length+' passed');
 if(results.some(r=>r.code!==0))process.exitCode=1;
}
