import {execFile,execFileSync} from 'node:child_process';
import {promisify} from 'node:util';
import assert from 'node:assert/strict';
const run=promisify(execFile);
const args=['exec','driveed_hub_app','php','tests/concurrent-session-fixture.php'];
const step=mode=>{
 const text=execFileSync('docker',[...args,mode],{encoding:'utf8'});
 assert(text.startsWith('PASS:'),text);
 console.log(text.trim());
};
step('prepare');
try{
 const workers=await Promise.all([0,1].map(index=>run('docker',[...args,'worker',String(index)],{encoding:'utf8'})));
 const results=workers.map(result=>JSON.parse(result.stdout));
 assert.deepEqual(results.map(result=>result.status).sort(),[200,422]);
 assert(results.find(result=>result.status===422).body.errors.hours_completed);
 console.log('PASS: Two simultaneous requests produce one success and one clear hour-limit rejection.');
 step('verify');
}finally{step('cleanup');}
