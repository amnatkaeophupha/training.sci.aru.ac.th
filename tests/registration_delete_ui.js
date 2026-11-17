// Run: node tests/registration_delete_ui.js. No browser, network or database needed.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const source = fs.readFileSync(require('node:path').join(__dirname, '../application/views/admins/registrations.php'), 'utf8');
const script = [...source.matchAll(/<script>([\s\S]*?)<\/script>/g)][0][1];
async function scenario(confirmed, library = true) {
    let listener, resolve, calls = 0, submitted = 0, prevented = 0, options;
    const button = { disabled: true };
    const form = {
        dataset: { name: '<script>test</script>', code: 'REG-12', course: 'Course A' },
        querySelector: () => button,
        addEventListener: (event, callback) => { listener = callback; }
    };
    const context = {
        document: { querySelectorAll: () => [form] },
        HTMLFormElement: { prototype: { submit() { assert.equal(this, form); submitted++; } } }
    };
    if (library) context.Swal = { fire(value) { options = value; calls++; return new Promise(done => { resolve = done; }); } };
    vm.runInNewContext(script, context);
    assert.equal(button.disabled, !library);
    const event = { preventDefault() { prevented++; } };
    listener(event);
    listener(event);
    assert.equal(prevented, 2);
    assert.equal(calls, library ? 1 : 0, 'Repeated clicks opened multiple confirmations');
    assert.equal(submitted, 0, 'Form submitted before confirmation');
    if (!library) return;
    assert.ok(options.text.includes('REG-12') && options.text.includes('Course A'));
    assert.equal(options.html, undefined, 'User values must be displayed as text');
    assert.equal(options.showCancelButton, true);
    resolve({ isConfirmed: confirmed });
    await Promise.resolve();
    assert.equal(submitted, confirmed ? 1 : 0);
    assert.equal(button.disabled, confirmed);
    if (!confirmed) { listener(event); assert.equal(calls, 2, 'Cancel did not re-enable confirmation'); }
    else { listener(event); assert.equal(submitted, 1, 'Confirmed form submitted twice'); }
}
(async () => {
    await scenario(false);
    await scenario(true);
    await scenario(false, false);
    console.log('PASS: SweetAlert cancel, confirm, duplicate clicks, text escaping and library failure');
})().catch(error => { console.error(error); process.exitCode = 1; });
