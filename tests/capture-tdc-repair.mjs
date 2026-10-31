import puppeteer from 'puppeteer-core';
import fs from 'node:fs/promises';
import {execFileSync} from 'node:child_process';
import {fileURLToPath} from 'node:url';

const output = new URL('../docs/screenshots/tdc-repair/', import.meta.url);
await fs.mkdir(output, {recursive: true});
const checks = [
    ['TDC passing lifecycle', ['php', 'tests/verify-tdc-completion.php']],
    ['Existing students and deployment migration', ['php', 'tests/verify-tdc-repair.php']],
    ['Repair command syntax', ['php', '-l', 'app/Console/Commands/RepairPassedTdcEnrollments.php']],
    ['Deployment migration syntax', ['php', '-l', 'database/migrations/2026_10_31_000001_repair_passed_standalone_tdc_enrollments.php']],
    ['Docker startup syntax', ['bash', '-n', 'docker/entrypoint.sh']],
];
const results = checks.map(([name, args]) => {
    const stdout = execFileSync('docker', ['exec', 'driveed_hub_app', ...args], {encoding: 'utf8'}).trim();
    console.log(`${name}: ${stdout || 'Exit code 0'}`);
    return {name, command: args.join(' '), stdout: stdout || 'Exit code 0'};
});
await fs.writeFile(new URL('results.json', output), JSON.stringify(results, null, 2));
const escape = value => value.replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;');
const html = `<!doctype html><html><head><meta charset="utf-8"><title>TDC deployment repair tests</title>
<style>body{margin:0;background:#f4f5f7;color:#162435;font:17px/1.5 Arial,sans-serif}main{max-width:1080px;margin:48px auto;padding:32px;background:white}h1{font-size:32px;margin:0 0 12px}p{color:#475569}article{border-top:1px solid #d8dfe6;padding:20px 0}h2{font-size:19px;margin:0 0 8px}.pass{color:#166534;margin-right:14px}pre{white-space:pre-wrap;font:15px/1.6 Consolas,monospace;margin:8px 0}code{color:#526175;font-size:14px}</style>
</head><body><main><h1>TDC deployment repair: test results</h1><p>Executed in Docker using an isolated in-memory SQLite database.<br>The running application's student records were not modified.</p>
${results.map(result => `<article><h2><span class="pass">PASS</span>${escape(result.name)}</h2><code>${escape(result.command)}</code><pre>${escape(result.stdout)}</pre></article>`).join('')}
<p>Deployment runs the data migration through <code>php artisan migrate --force</code>.<br>Standalone passed TDC enrollments complete; Combo enrollments and training logs remain intact.</p></main></body></html>`;
await fs.writeFile(new URL('results.html', output), html);
const browser = await puppeteer.launch({executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe', headless: true, args: ['--no-sandbox']});
try {
    const page = await browser.newPage();
    await page.setViewport({width: 1280, height: 1050, deviceScaleFactor: 1});
    await page.setContent(html, {waitUntil: 'load'});
    await page.screenshot({path: fileURLToPath(new URL('test-results.png', output)), fullPage: true});
} finally {
    await browser.close();
}
console.log('Screenshot saved to docs/screenshots/tdc-repair/test-results.png');
