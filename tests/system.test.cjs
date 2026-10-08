const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const {test} = require('node:test');
const {JSDOM} = require('jsdom');

const root = path.join(__dirname, '..');
const local = JSON.parse(fs.readFileSync(path.join(root, 'release.json'), 'utf8'));
const php = fs.readFileSync(path.join(root, 'index.php'), 'utf8');
const script = php.slice(php.indexOf('const STUDIO_VERSIONS'), php.lastIndexOf('window.Studio = new PDFStudio();'));

function environment(t, url = 'https://example.test/pdf/index.php') {
  const dom = new JSDOM('<!doctype html><button id="studioUpdatesButton"></button><span id="studioUpdateBadge" hidden></span><div id="documentContent">Unsaved document</div><div id="toolModal"><div class="modal-dialog"><h2 id="toolModalTitle"></h2><div id="toolModalBody"></div><button id="toolSubmit"></button><div id="modalError" hidden></div></div></div>', {url, runScripts:'outside-only'});
  const w = dom.window;
  w.PDF_STUDIO_BOOT = {version:local.version, release:local};
  w.bootstrap = {Modal:{getOrCreateInstance:()=>({show(){}}),getInstance:()=>({hide(){throw new Error('System dialogs must stay open while their content changes.');}})}};
  w.eval(script + '\nwindow.PDFStudioClass=PDFStudio;window.StudioUpdatesClass=StudioUpdates;');
  const studio = new w.PDFStudioClass();
  const messages = [];
  studio.notify = message => messages.push(message);
  studio.server = {php:'7.2.34', tools:{
    qpdf:{label:'qpdf', source:'server', available:false, version:null, features:['Passwords'], reason:'PHP proc_open is disabled.', install:{commands:['sudo apt update','sudo apt install -y qpdf'], notes:['Enable proc_open before using this tool.']}},
    gs:{label:'Ghostscript', source:'server', available:true, version:'10.0', features:['Compression'], install:{commands:['sudo apt install -y ghostscript'],notes:[]}}
  }};
  studio.updates = new w.StudioUpdatesClass(studio, local);
  t.after(() => w.close());
  return {w, studio, messages, updates:studio.updates};
}

function remote(version='1.2.0', changes=['A useful new feature.'], minimumPhp='7.2') {
  return {version, minimumPhp, releases:[{version,date:'2026-10-09',changes},...local.releases]};
}

test('capabilities identify Browser and Server and offer install help only for missing server tools', async t => {
  const {w,studio} = environment(t);
  studio.capabilitiesDialog();
  assert.equal(w.document.querySelectorAll('[data-source="browser"]').length, 7);
  assert.equal(w.document.querySelectorAll('[data-source="server"]').length, 2);
  const buttons = w.document.querySelectorAll('[data-action="install-help"]');
  assert.equal(buttons.length, 1);
  assert.equal(buttons[0].dataset.tool, 'qpdf');
  assert.match(buttons[0].getAttribute('aria-label'), /Ubuntu installation help/);
  assert.match(w.document.querySelector('#toolModalBody').textContent, /PHP proc_open is disabled/);
  assert.equal(w.document.querySelectorAll('[data-source="browser"] [data-action="install-help"]').length, 0);
  let copied = '';
  Object.defineProperty(w.navigator, 'clipboard', {value:{writeText:async text => {copied=text;}}});
  w.fetch = () => {throw new Error('Install help must not send a request.');};
  await studio.action('install-help', buttons[0]);
  await Promise.resolve();
  assert.equal(w.document.querySelector('#studioInstallCommands').value, 'sudo apt update\nsudo apt install -y qpdf');
  w.document.querySelector('#studioCopyInstall').click();
  await Promise.resolve();
  assert.equal(copied, 'sudo apt update\nsudo apt install -y qpdf');
});

test('upgrade notes appear once and contain only changes since the previous version', t => {
  const {w,studio,updates} = environment(t);
  w.localStorage.setItem(updates.storageKey, JSON.stringify({seenVersion:'1.0.0'}));
  const first = new w.StudioUpdatesClass(studio, local);
  first.announceUpgrade();
  const body = w.document.querySelector('#toolModalBody').textContent;
  assert.match(body, /upgraded from 1\.0\.0 to 1\.1\.0/);
  assert.match(body, /Ubuntu installation commands/);
  assert.doesNotMatch(body, /Word-like editor/);
  assert.equal(JSON.parse(w.localStorage.getItem(first.storageKey)).seenVersion, '1.1.0');
  w.document.querySelector('#toolModalTitle').textContent = 'Untouched';
  new w.StudioUpdatesClass(studio, local).announceUpgrade();
  assert.equal(w.document.querySelector('#toolModalTitle').textContent, 'Untouched');
  first.showChangelog();
  assert.match(w.document.querySelector('#toolModalBody').textContent, /Word-like editor/);
});

test('manual update check is deduplicated, uses GitHub without credentials and displays safe newer notes', async t => {
  const {w,updates,studio} = environment(t);
  let calls = 0;
  w.fetch = async (url, options) => {
    calls++;
    assert.equal(url, 'https://raw.githubusercontent.com/ziobit/zbpdfstudio/main/release.json');
    assert.equal(options.credentials, 'omit');
    assert.equal(options.referrerPolicy, 'no-referrer');
    assert.equal(options.body, undefined);
    return {ok:true, text:async()=>JSON.stringify(remote('1.2.0',['Better exports.','<img src=x onerror=alert(1)>']))};
  };
  updates.show();
  await updates.check(true);
  assert.equal(calls, 1);
  assert.equal(w.document.querySelector('#studioUpdateBadge').hidden, false);
  const panel = w.document.querySelector('#studioUpdatePanel');
  assert.match(panel.textContent, /Version 1\.2\.0 is available/);
  assert.match(panel.textContent, /Better exports/);
  assert.equal(panel.querySelector('img'), null);
  assert.doesNotMatch(panel.textContent, /See which capabilities/);
  assert.equal(panel.querySelector('a[download]').href, 'https://raw.githubusercontent.com/ziobit/zbpdfstudio/main/index.php');
  assert.equal(w.document.querySelector('#documentContent').textContent, 'Unsaved document');
  const reopened = new w.StudioUpdatesClass(studio, local);
  await reopened.check(false);
  assert.equal(calls, 1);
  reopened.updateBadge();
  assert.equal(w.document.querySelector('#studioUpdateBadge').hidden, false);
});

test('daily automatic checks can be forced manually and cache is scoped to each installation', async t => {
  const {w,updates,studio} = environment(t);
  let calls = 0;
  w.fetch = async()=>{calls++;return {ok:true,text:async()=>JSON.stringify(local)};};
  await updates.check(false);
  await updates.check(false);
  assert.equal(calls, 1);
  await updates.check(true);
  assert.equal(calls, 2);
  const different = new w.StudioUpdatesClass(studio, local);
  w.history.replaceState(null, '', '/another/index.php');
  const other = new w.StudioUpdatesClass(studio, local);
  assert.notEqual(other.storageKey, different.storageKey);
  assert.equal(other.state.lastAttemptAt, undefined);
  updates.state.lastAttemptAt = Date.now() - 86400001;
  await updates.check(false);
  assert.equal(calls, 3);
});

test('network failures and invalid metadata do not claim the application is up to date', async t => {
  const {w,updates} = environment(t);
  w.fetch = async()=>{throw new Error('Network unavailable');};
  updates.show();
  await updates.check(true);
  assert.match(w.document.querySelector('#studioUpdatePanel').textContent, /Could not check for updates/);
  assert.doesNotMatch(w.document.querySelector('#studioUpdatePanel').textContent, /latest published version\./);
  assert.equal(updates.state.manifest, undefined);
  w.fetch = async()=>({ok:true,text:async()=>JSON.stringify({version:'1.2.0',minimumPhp:'7.2',releases:[]})});
  await updates.check(true);
  assert.match(updates.error, /invalid/);
  assert.equal(updates.pending, null);
  assert.equal(w.document.querySelector('#documentContent').textContent, 'Unsaved document');
});

test('slow update checks abort and allow a retry', async t => {
  const {w,updates} = environment(t);
  const original = w.setTimeout.bind(w);
  w.setTimeout = (callback, ms)=>original(callback, ms===8000?1:ms);
  w.fetch = (url,options)=>new Promise((resolve,reject)=>options.signal.addEventListener('abort',()=>reject(new w.DOMException('Aborted','AbortError'))));
  await updates.check(true);
  assert.match(updates.error, /timed out/);
  assert.equal(updates.pending, null);
  w.fetch = async()=>({ok:true,text:async()=>JSON.stringify(local)});
  await updates.check(true);
  assert.equal(updates.error, '');
});

test('a PHP requirement is shown before the user replaces the server file', async t => {
  const {w,updates} = environment(t);
  w.fetch = async()=>({ok:true,text:async()=>JSON.stringify(remote('1.2.0',['New export tools.'],'8.2'))});
  updates.show();
  await updates.check(true);
  assert.match(w.document.querySelector('#studioUpdatePanel').textContent, /needs PHP 8\.2 or newer/);
  assert.match(w.document.querySelector('#studioUpdatePanel').textContent, /Upgrade PHP before replacing index.php/);
});

test('blocked browser storage does not stop updates or release notes', async t => {
  const {w,studio} = environment(t);
  Object.defineProperty(w, 'localStorage', {get(){throw new Error('Storage blocked');}});
  const updates = new w.StudioUpdatesClass(studio, local);
  assert.doesNotThrow(()=>updates.announceUpgrade());
  w.fetch = async()=>({ok:true,text:async()=>JSON.stringify(local)});
  await updates.check(true);
  assert.equal(updates.remote.version, local.version);
  assert.equal(updates.error, '');
});

test('versions are compared numerically and mismatched release metadata is rejected', t => {
  const {updates} = environment(t);
  assert.equal(updates.compare('1.10.0','1.9.9'), 1);
  assert.equal(updates.compare('7.2.34','7.2'), 1);
  assert.equal(updates.compare('1.1.0','1.1.0'), 0);
  const wrong = remote();wrong.version = '1.3.0';
  assert.throws(()=>updates.validateManifest(wrong), /does not match/);
});
