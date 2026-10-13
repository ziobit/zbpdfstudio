const assert=require('node:assert/strict'),fs=require('node:fs'),path=require('node:path'),vm=require('node:vm');
const {test,before}=require('node:test'),PDFLib=require('pdf-lib');
const root=path.join(__dirname,'..'),php=fs.readFileSync(path.join(root,'index.php'),'utf8');
const code=php.slice(php.indexOf('class NativePDFEditor {'),php.indexOf('class DocumentStudio {'));
let engine;
before(async()=>{const {init}=await import('@embedpdf/pdfium');const wasm=path.join(path.dirname(require.resolve('@embedpdf/pdfium')),'pdfium.wasm');engine=await init({wasmBinary:fs.readFileSync(wasm)});engine.PDFiumExt_Init();});
function environment(){
  const nodes=new Map(),document={querySelector:selector=>{if(!nodes.has(selector))nodes.set(selector,{textContent:'',setAttribute(){}});return nodes.get(selector);}};
  const pdfjsLib={getDocument:()=>({promise:Promise.resolve({}),destroy:async()=>{}})};
  const classes=vm.compileFunction('const CDN="https://example.test/assets/";\n'+code+'\nreturn {Editor:NativePDFEditor,StudioClass:PDFStudio};',['window','PDFLib','pdfjsLib','document'])({PDFLib,pdfjsLib},PDFLib,pdfjsLib,document);
  const studio=new classes.StudioClass();studio.nativeEditor.engine=engine;studio.notify=()=>{};studio.inspector=()=>{};studio.updateLayers=()=>{};return {studio,editor:studio.nativeEditor};
}
async function sample(){
  const doc=await PDFLib.PDFDocument.create(),page=doc.addPage([500,500]);
  page.drawRectangle({x:30,y:30,width:300,height:300,color:PDFLib.rgb(0,.5,0)});page.drawText('Original text',{x:40,y:400,size:20});page.drawText('Keep this text',{x:40,y:360,size:15});
  const image=await doc.embedPng(Buffer.from('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aWZkAAAAASUVORK5CYII=','base64'));page.drawImage(image,{x:390,y:100,width:50,height:50});return {doc,page,bytes:await doc.save()};
}
const elements=async(editor,bytes)=>(await editor.rewrite(bytes)).elements,plain=value=>JSON.parse(JSON.stringify(value));
test('existing text is replaced as real searchable text without moving its neighbors',async()=>{
  const {editor}=environment(),{bytes}=await sample(),base=await elements(editor,bytes),target=base.find(e=>e.text==='Original text');
  const output=await editor.rewrite(bytes,[{path:target.path,action:'text',text:'Caffè für Köln',font:'original'}]),saved=await elements(editor,output.bytes);
  assert.ok(saved.some(e=>e.text.trimEnd()==='Caffè für Köln'));assert.ok(!saved.some(e=>e.text.includes('Original text')));
  assert.deepEqual(plain(saved.find(e=>e.text==='Keep this text').quad),plain(base.find(e=>e.text==='Keep this text').quad));assert.equal(saved.filter(e=>e.type===3).length,1);
});
test('individual text image and vector deletions survive saving',async()=>{
  const {editor}=environment(),{bytes}=await sample(),base=await elements(editor,bytes);
  for(const type of [1,2,3]){const target=base.find(e=>e.type===type),saved=await elements(editor,(await editor.rewrite(bytes,[{path:target.path,action:'delete'}])).bytes);assert.equal(saved.length,base.length-1);assert.ok(saved.some(e=>e.text==='Keep this text'));}
});
test('explicit replacement font supports accents and preserves font size',async()=>{
  const {editor}=environment(),{bytes}=await sample(),base=await elements(editor,bytes),target=base.find(e=>e.text==='Original text');
  const saved=await elements(editor,(await editor.rewrite(bytes,[{path:target.path,action:'text',text:'È già così, grüß dich!',font:'Times-Bold'}])).bytes),replacement=saved.find(e=>e.text.trimEnd()==='È già così, grüß dich!');
  assert.ok(replacement);assert.equal(replacement.font,'Times-Bold');assert.equal(replacement.size,target.size);
});
test('unsupported characters fail without changing the original document',async()=>{
  const {editor}=environment(),{bytes}=await sample(),base=await elements(editor,bytes),target=base.find(e=>e.text==='Original text');
  await assert.rejects(editor.rewrite(bytes,[{path:target.path,action:'text',text:'漢字',font:'original'}]),/font cannot display/);assert.ok((await elements(editor,bytes)).some(e=>e.text==='Original text'));
});
test('editing or deleting a child of a reused PDF group affects only that occurrence',async()=>{
  const {editor}=environment(),{page}=await sample(),doc=await PDFLib.PDFDocument.create(),embedded=await doc.embedPage(page),p=doc.addPage([1000,600]);p.drawPage(embedded,{x:0,y:0,width:500,height:500});p.drawPage(embedded,{x:500,y:0,width:500,height:500});
  const bytes=await doc.save(),base=await elements(editor,bytes),target=base.find(e=>e.text==='Original text');assert.equal(target.path.length,2);
  for(const edit of [{action:'text',text:'Only first',font:'original'},{action:'delete'}]){const saved=await elements(editor,(await editor.rewrite(bytes,[{path:target.path,...edit}])).bytes);assert.equal(saved.filter(e=>e.text.trimEnd()==='Original text').length,1);assert.equal(saved.filter(e=>e.text.trimEnd()==='Keep this text').length,2);if(edit.action==='text')assert.equal(saved.filter(e=>e.text.trimEnd()==='Only first').length,1);}
});
test('ordered replay keeps references valid after deleting an earlier object',async()=>{
  const {editor}=environment(),{bytes}=await sample(),base=await elements(editor,bytes),first={action:'delete',path:base.find(e=>e.type===2).path};
  const after=await editor.rewrite(bytes,[first]),target=after.elements.find(e=>e.text==='Keep this text'),second={action:'text',path:target.path,text:'Second edit',font:'original'};
  const saved=await elements(editor,(await editor.rewrite(bytes,[first,second])).bytes);assert.equal(saved.filter(e=>e.type===2).length,0);assert.ok(saved.some(e=>e.text.trimEnd()==='Second edit'));
});
test('export retains interactive form fields and annotations',async()=>{
  const {studio,editor}=environment(),{doc,page}=await sample(),field=doc.getForm().createTextField('customer');field.setText('Ada');field.addToPage(page,{x:40,y:200,width:200,height:30});
  const bytes=await doc.save(),lib=await PDFLib.PDFDocument.load(bytes),base=await elements(editor,bytes),target=base.find(e=>e.text==='Original text');studio.sources.set('source',{id:'source',bytes,lib});
  studio.pages=[{id:'a',sourceId:'source',index:0,rotation:0,width:500,height:500,nativeEdits:[{action:'text',path:target.path,text:'New text',font:'original'}]}];
  const exported=await studio.exportBytes(),output=await PDFLib.PDFDocument.load(exported);assert.equal(output.getForm().getTextField('customer').getText(),'Ada');assert.ok(output.getPage(0).node.Annots().size()>0);assert.ok((await elements(editor,exported)).some(e=>e.text.trimEnd()==='New text'));
});
test('copied pages have independent edits and retain rotations',async()=>{
  const {studio,editor}=environment(),{bytes}=await sample(),lib=await PDFLib.PDFDocument.load(bytes),base=await elements(editor,bytes),target=base.find(e=>e.text==='Original text');studio.sources.set('source',{id:'source',bytes,lib});const page={sourceId:'source',index:0,width:500,height:500};
  studio.pages=[{...page,id:'a',rotation:90,nativeEdits:[{action:'delete',path:target.path}]},{...page,id:'b',rotation:270}];const output=await PDFLib.PDFDocument.load(await studio.exportBytes());assert.equal(output.getPage(0).getRotation().angle,90);assert.equal(output.getPage(1).getRotation().angle,270);
  for(let i=0;i<2;i++){const one=await PDFLib.PDFDocument.create();one.addPage((await one.copyPages(output,[i]))[0]);assert.equal((await elements(editor,await one.save())).some(e=>e.text.trimEnd()==='Original text'),i===1);}
});
test('history and projects retain changes and reject malformed edit references',async()=>{
  const {studio,editor}=environment(),{bytes}=await sample(),lib=await PDFLib.PDFDocument.load(bytes),base=await elements(editor,bytes),target=base.find(e=>e.text==='Original text');studio.sources.set('source',{id:'source',name:'sample.pdf',bytes,lib,hash:'original-hash'});
  studio.pages=[{id:'a',sourceId:'source',index:0,width:500,height:500,rotation:0}];studio.commit();studio.pages[0].nativeEdits=[{action:'delete',path:target.path}];studio.commit();assert.deepEqual(plain(studio.project(false).pages[0].nativeEdits),plain(studio.history[1].pages[0].nativeEdits));assert.equal(studio.history[0].pages[0].nativeEdits,undefined);clearTimeout(studio.autosaveTimer);
  for(const edits of [[{action:'delete',path:[-1]}],[{action:'delete',path:[.5]}],[{action:'text',path:[1],text:'a\u0000b',font:'original'}],[{action:'text',path:[1],text:'a',font:'<script>'}]])assert.throws(()=>editor.validate(edits),/Invalid original PDF/);
});
test('editing text must preserve shading on the same content stream',async()=>{
  const {editor}=environment(),{bytes}=await sample(),doc=await PDFLib.PDFDocument.load(bytes),p=doc.getPage(0),ctx=doc.context,name=PDFLib.PDFName.of;
  const gradient=ctx.register(ctx.obj({ShadingType:2,ColorSpace:'DeviceRGB',Coords:[0,0,300,0],Function:{FunctionType:2,Domain:[0,1],C0:[1,0,0],C1:[0,0,1],N:1},Extend:[true,true]}));p.node.Resources().set(name('Shading'),ctx.obj({Gradient:gradient}));
  const text=p.node.Contents().asArray().map(ref=>Buffer.from(PDFLib.decodePDFRawStream(ctx.lookup(ref)).decode()).toString('latin1')).join('\n');p.node.set(name('Contents'),ctx.register(ctx.flateStream(text+'\n/Gradient sh\n')));
  const input=await doc.save(),base=await elements(editor,input),target=base.find(e=>e.text==='Original text');assert.equal(base.filter(e=>e.type===4).length,1);
  const changed=await editor.rewrite(input,[{action:'text',path:target.path,text:'New text',font:'original'}]);const saved=await elements(editor,changed.bytes);assert.equal(saved.filter(e=>e.type===4).length,1);
});
