// Run: node tests/certificate_template_ui.js. No browser or live writes.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const elements = {};
function element(id, value = '') {
    const listeners = {};
    return elements[id] = { value, style: {}, listeners, clientWidth: 200, clientHeight: 500, offsetWidth: 200, offsetHeight: 20,
        addEventListener(name, fn) { listeners[name] = fn; },
        classList: { add() {}, remove() {} }, setPointerCapture() {},
        getBoundingClientRect() { return {left: 0, top: 0}; },
        querySelector() { return this.text; }
    };
}
element('preview'); elements.preview.clientWidth = 800; elements.preview.clientHeight = 800 * 210 / 297;
for (const [id, value] of Object.entries({name_x:10,name_y:45,name_width:80,font_size:30,font_color:'#172033',number_x:70.707,number_y:93.333,number_width:25.253,number_font_size:9,number_font_color:'#282828',number_align:'R'})) element(id, String(value));
const name = element('name-box'), number = element('number-box'); number.text = {style:{}, offsetWidth:160};
const background = element('background');
const window = {addEventListener() {}};
vm.runInNewContext(fs.readFileSync(require('node:path').join(__dirname,'../assets/js/certificate-template.js'),'utf8'),{
    document:{getElementById:id=>elements[id],querySelector:()=>background}, window, URL:{}
});
assert.equal(number.style.justifyContent, 'flex-end');
assert.equal(elements.name_x.value, '10.000');
number.listeners.pointerdown({button:0,pointerId:1,clientX:0,clientY:0,preventDefault(){}});
number.listeners.pointermove({clientX:10000,clientY:10000});
assert.ok(Number(elements.number_x.value)+Number(elements.number_width.value)<=100.001);
assert.ok(Number(elements.number_y.value)<100);
assert.equal(elements.name_x.value,'10.000','Number dragging moved the name');
number.listeners.pointercancel(); const previous = elements.number_x.value;
number.listeners.pointermove({clientX:0,clientY:0}); assert.equal(elements.number_x.value, previous);
elements.number_align.value='L'; elements.number_align.listeners.input(); assert.equal(number.style.justifyContent,'flex-start');
elements.number_font_color.value='#123456'; elements.number_font_color.listeners.input(); assert.equal(number.style.color,'#123456');
assert.equal(name.style.color,'#172033','Number color altered the name');
console.log('PASS: independent name/number controls, dragging bounds, pointer cancellation, alignment and color');
