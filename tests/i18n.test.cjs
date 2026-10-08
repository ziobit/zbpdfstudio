const assert=require('node:assert/strict');
const fs=require('node:fs');
const path=require('node:path');
const {test}=require('node:test');
const {JSDOM,VirtualConsole}=require('jsdom');
const root=path.join(__dirname,'..');
const source=fs.readFileSync(path.join(root,'index.php'),'utf8');
const catalog=JSON.parse(fs.readFileSync(path.join(root,'translations.json'),'utf8'));
const release=JSON.parse(fs.readFileSync(path.join(root,'release.json'),'utf8'));
const script=source.slice(source.indexOf('const STUDIO_VERSIONS'),source.lastIndexOf('window.Studio = new PDFStudio();'));
const markup=source.slice(source.indexOf('<body>'),source.indexOf('<script>window.PDF_STUDIO_BOOT'));
function environment(t,url='https://example.test/studio/index.php',extra=''){
  const dom=new JSDOM(markup+extra,{url,runScripts:'outside-only',virtualConsole:new VirtualConsole()});const w=dom.window;
  w.PDF_STUDIO_BOOT={language:'it',translations:structuredClone(catalog),release,version:release.version};
  w.bootstrap={Modal:{getOrCreateInstance:()=>({show(){}})}};
  w.eval(script+'\nwindow.PDFStudioClass=PDFStudio;window.PDFToolsClass=PDFTools;');
  const studio=new w.PDFStudioClass();w.Studio=studio;studio.notify=()=>{};
  t.after(()=>{w.StudioLanguage.observer.disconnect();w.close();});return{w,studio,lang:w.StudioLanguage};
}
test('Italian is the initial language and UI controls can switch both ways without reloading',async t=>{
  const {w,lang}=environment(t);
  assert.equal(w.document.documentElement.lang,'it');
  assert.equal(w.document.querySelector('[data-action="export"]').textContent.trim(),'Esporta PDF');
  const input=w.document.querySelector('#toolModalBody');input.innerHTML='<label>Page size<select><option>left</option><option>right</option></select></label><input name="title" value="Home Text">';
  await Promise.resolve();
  assert.equal(input.querySelector('option').textContent,'sinistra');
  assert.equal(input.querySelector('select').value,'left');
  lang.setLanguage('de');
  assert.equal(w.document.querySelector('[data-action="export"]').textContent.trim(),'PDF exportieren');
  assert.equal(input.querySelector('input').value,'Home Text');
  assert.equal(input.querySelector('select').value,'left');
  lang.setLanguage('en');
  assert.equal(w.document.querySelector('[data-action="export"]').textContent.trim(),'Export PDF');
  assert.equal(input.querySelector('option').textContent,'left');
  lang.setLanguage('it');assert.equal(input.querySelector('option').textContent,'sinistra');
});
test('document content commands and filenames are untouched by translations',async t=>{
  const {w,lang}=environment(t,undefined,'<div contenteditable="true">Home Text Save project</div><pre>PDF report Page Text</pre><span translate="no">Home</span><textarea id="testCommands">sudo apt install -y qpdf</textarea>');
  for(const language of ['it','de','en']){
    lang.setLanguage(language);
    assert.equal(w.document.querySelector('[contenteditable]').textContent,'Home Text Save project');
    assert.equal(w.document.querySelector('pre').textContent,'PDF report Page Text');
    assert.equal(w.document.querySelector('[translate="no"]').textContent,'Italiano');
    assert.equal(w.document.querySelector('#testCommands').value,'sudo apt install -y qpdf');
    assert.ok(lang.text('Unsupported file: Home.pdf').endsWith('Home.pdf'));
  }
});
test('new dialogs errors status and release notes are translated after asynchronous insertion',async t=>{
  const {w,lang,studio}=environment(t);
  studio.dialog('System capabilities','<p>Version 1.3.0 is available.</p><p>Run PDF Studio on PHP 7.2 and newer servers.</p><input placeholder="Search tools">',null,{submit:false});
  await Promise.resolve();
  assert.equal(w.document.querySelector('#toolModalTitle').textContent,'Capacità del sistema');
  assert.match(w.document.querySelector('#toolModalBody').textContent,/La versione 1\.3\.0 è disponibile/);
  assert.equal(w.document.querySelector('#toolModalBody input').placeholder,'Cerca strumenti');
  lang.setLanguage('de');
  assert.match(w.document.querySelector('#toolModalBody').textContent,/Version 1\.3\.0 ist verfügbar/);
  w.document.querySelector('#toolModalTitle').textContent='Fill PDF forms';await Promise.resolve();
  assert.equal(w.document.querySelector('#toolModalTitle').textContent,'PDF-Formulare ausfüllen');
});
test('language is explicit remembered locally and independent from browser locale',t=>{
  const {w,lang}=environment(t,'https://example.test/studio/index.php?lang=de');
  assert.equal(lang.language,'de');lang.setLanguage('en');
  assert.match(w.document.cookie,/PDFSTUDIOLANG=en/);
  assert.equal(w.localStorage.getItem(lang.key),'en');
  w.history.replaceState(null,'','/studio/index.php');
  assert.equal(new w.StudioLanguage.constructor(structuredClone(catalog),'it').language,'en');
  w.history.replaceState(null,'','/other/index.php');
  assert.equal(new w.StudioLanguage.constructor(structuredClone(catalog),'it').language,'it');
  assert.equal(w.document.cookie.includes('PDFSTUDIOLANG'),false);
});
test('native editor wording switches languages without touching the document',async t=>{
  const {w,lang}=environment(t);
  lang.registerEditor({it:{'Open':'Aprire file'},de:{'Open':'Datei öffnen'}});
  const toolbar=w.document.createElement('button');toolbar.textContent='Aprire file';w.document.body.append(toolbar);
  await Promise.resolve();
  assert.equal(toolbar.textContent,catalog.Open.it);
  lang.setLanguage('en');assert.equal(toolbar.textContent,'Open');
  lang.setLanguage('de');assert.equal(toolbar.textContent,catalog.Open.de);
});
test('no database is needed for autosave recovery and blocked storage does not stop editing',async t=>{
  const {w,studio,lang}=environment(t);
  Object.defineProperty(w,'indexedDB',{get(){throw new Error('No database allowed');}});
  const project={format:'pdfstudio-project',pages:[],sources:[]};
  studio.project=()=>project;
  await studio.saveAutosave();assert.equal(JSON.stringify(await studio.readAutosave()),JSON.stringify(project));
  await studio.writeAutosave(null);assert.equal(await studio.readAutosave(),null);
  Object.defineProperty(w,'localStorage',{get(){throw new Error('Storage blocked');}});
  assert.doesNotThrow(()=>lang.setLanguage('de'));
  await assert.doesNotReject(()=>studio.saveAutosave());assert.equal(studio.autosaveWarning,true);
});
test('Italian and German page range keywords preserve the original selection semantics',t=>{
  const {studio}=environment(t);
  assert.deepEqual(Array.from(studio.range('tutte',4)),[0,1,2,3]);
  assert.deepEqual(Array.from(studio.range('alle',4)),[0,1,2,3]);
  for(const word of ['odd','dispari','ungerade'])assert.deepEqual(Array.from(studio.range(word,4)),[0,2]);
  for(const word of ['even','pari','gerade'])assert.deepEqual(Array.from(studio.range(word,4)),[1,3]);
});
test('tool search works with localized names and keeps action identifiers unchanged',async t=>{
  const {w,studio,lang}=environment(t);
  studio.toolDefs=[{id:'watermark',title:'Watermark',group:'Advanced',requirements:'browser'},{id:'crop',title:'Crop pages',group:'Organize',requirements:'browser'}];studio.available=()=>true;
  studio.palette();await Promise.resolve();
  const query=w.document.querySelector('#paletteQuery');query.value='filigrana';query.dispatchEvent(new w.Event('input'));await Promise.resolve();
  assert.equal(w.document.querySelector('#paletteResults button').dataset.action,'watermark');
  lang.setLanguage('de');query.value='Wasserzeichen';query.dispatchEvent(new w.Event('input'));await Promise.resolve();
  assert.equal(w.document.querySelector('#paletteResults button').dataset.action,'watermark');
});
test('every catalog translation retains its dynamic placeholders',()=>{
  for(const [source,languages]of Object.entries(catalog))for(const language of ['it','de']){
    assert.ok(languages[language]);
    const tokens=s=>(s.match(/\{\d+\}|\{\{\w+\}\}|%[sd]/g)||[]).sort();
    assert.deepEqual(tokens(languages[language]),tokens(source),source+' '+language);
  }
});
test('new visible dialog labels and tool titles must have both translations',()=>{
  const acorn=require('acorn');const tree=acorn.parse(script,{ecmaVersion:'latest'});const labels=new Set();
  function walk(node){
    if(!node||typeof node!=='object')return;
    if(node.type==='CallExpression'&&node.callee.type==='MemberExpression'&&['dialog','field','select','check','notify'].includes(node.callee.property.name)&&node.arguments[0]?.type==='Literal'&&typeof node.arguments[0].value==='string')labels.add(node.arguments[0].value);
    if(node.type==='Property'&&['title','description'].includes(node.key.name)&&node.value.type==='Literal'&&typeof node.value.value==='string'&&node.value.value.trim())labels.add(node.value.value);
    for(const value of Object.values(node))if(Array.isArray(value))value.forEach(walk);else if(value&&typeof value==='object')walk(value);
  }
  walk(tree);
  for(const label of labels)assert.ok(catalog[label],label+' needs Italian and German translations');
});
