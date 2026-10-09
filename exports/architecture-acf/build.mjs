import {readFileSync,writeFileSync} from 'node:fs';
import {fileURLToPath} from 'node:url';
import {dirname,join} from 'node:path';
import vm from 'node:vm';
const root=dirname(fileURLToPath(import.meta.url));
const read=name=>readFileSync(join(root,name),'utf8');
const schemaSource=read('schema.js');
const context={};vm.createContext(context);vm.runInContext(schemaSource+'\nthis.model = schema;',context);
const model=context.model;
const assert=(condition,message)=>{if(!condition)throw new Error(message);};
assert(model.contents.length===11,'11 types attendus');assert(model.options.length===3,'3 pages options attendues');assert(model.taxonomies.length===5,'5 taxonomies attendues');
const all=[...model.contents,...model.options,model.common];
assert(all.length===15,'15 groupes attendus');
assert(new Set(all.map(x=>x.id)).size===15,'Identifiants dupliqués');
assert(new Set(all.map(x=>x.group)).size===15,'Clés de groupes dupliquées');
const targets=new Set([...all.map(x=>x.id),'page']);
let total=0;
function check(fields){const names=new Set();for(const field of fields){assert(field.label&&field.name&&field.type,'Champ incomplet');assert(!names.has(field.name),'Champ dupliqué '+field.name);names.add(field.name);assert(/^[a-z0-9_]+$/.test(field.name),'Identifiant invalide');for(const target of field.targets||[])assert(targets.has(target),'Relation inconnue '+target);if(field.children)check(field.children);total++;}}
for(const item of all){check(item.fields);for(const tax of item.taxonomy||[])assert(model.taxonomies.some(x=>x.id===tax),'Taxonomie inconnue');}
for(const content of model.contents)assert(content.id.length<=20,'Clé CPT trop longue');
const app=read('app.js');new vm.Script(app);
const css=read('styles.css').replace('.layout{grid-template-columns:230px minmax(0,1fr))}','');
const html=read('template.html').replace('/* STYLES */',()=>css).replace('/* SCHEMA */',()=>schemaSource).replace('/* APP */',()=>app);
assert(!html.includes('/* APP */'),'Assemblage incomplet');
writeFileSync(join(root,'index.html'),html,'utf8');
console.log(JSON.stringify({types:model.contents.length,groups:all.length,taxonomies:model.taxonomies.length,options:model.options.length,fieldsIncludingSubfields:total,output:join(root,'index.html')},null,2));
