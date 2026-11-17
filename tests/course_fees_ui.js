// Run: node tests/course_fees_ui.js. Exercises real editor event handlers with a small DOM double.
const assert = require('node:assert/strict');
const vm = require('node:vm');
const fs = require('node:fs');
const controls = {};
function input(value) { return {value, listeners:{},addEventListener(name,fn){this.listeners[name]=fn;}}; }
function row(saved,label) {
    const item={dataset:{saved:String(saved)},fields:['id','label','amount','is_active'].map((key,i)=>({...input([String(saved),label,'200.00','1'][i]),dataset:{field:key}})),querySelectorAll(){return this.fields;},remove(){rows.items.splice(rows.items.indexOf(this),1);}};
    Object.defineProperty(item,'previousElementSibling',{get(){return rows.items[rows.items.indexOf(this)-1];}});
    Object.defineProperty(item,'nextElementSibling',{get(){return rows.items[rows.items.indexOf(this)+1];}});
    return item;
}
const rows={items:[],listeners:{},querySelectorAll(){return this.items;},addEventListener(name,fn){this.listeners[name]=fn;},appendChild(item){this.items.push(item);},insertBefore(item,before){this.items.splice(this.items.indexOf(item),1);this.items.splice(this.items.indexOf(before),0,item);}};
controls.fee_mode=input('fixed'); controls.fee=input('300'); controls['fee-options-panel']={}; controls['fixed-fee-field']={}; controls['fee-options-rows']=rows;
controls['add-fee-option']=input(''); controls['fee-option-template']={content:{cloneNode(){return row(0,'New');}}};
rows.items.push(row(8,'Existing'));
vm.runInNewContext(fs.readFileSync(require('node:path').join(__dirname,'../assets/js/course-fees.js'),'utf8'),{document:{getElementById:id=>controls[id]}});
assert.equal(controls['fee-options-panel'].hidden,true);
controls.fee_mode.value='options'; controls.fee_mode.listeners.change();
assert.equal(controls['fixed-fee-field'].hidden,true);
controls['add-fee-option'].listeners.click(); assert.equal(rows.items.length,2);
assert.equal(rows.items[1].fields[1].name,'fee_options[1][label]'); assert.equal(rows.items[1].fields[1].required,true);
function click(item,move,remove=false) { const button={dataset:{move},hasAttribute:()=>remove,closest:()=>item}; rows.listeners.click({target:{closest:()=>button}}); }
click(rows.items[1],'up'); assert.equal(rows.items[0].fields[1].value,'New'); assert.equal(rows.items[0].fields[1].name,'fee_options[0][label]');
click(rows.items[1],undefined,true); assert.equal(rows.items.length,2,'Saved condition was removed');
click(rows.items[0],undefined,true); assert.equal(rows.items.length,1);
assert.equal(rows.items[0].fields[0].value,'8');
console.log('PASS: price mode, adding/reordering conditions, field names and removal of unsaved rows only');
