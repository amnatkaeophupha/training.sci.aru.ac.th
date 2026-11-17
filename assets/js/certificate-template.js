(function () {
    'use strict';
    var preview = document.getElementById('preview');
    if (!preview) return;
    function field(id) { return document.getElementById(id); }
    function bind(box, x, y, width, size, color, align) {
        var dragging = false, dx = 0, dy = 0;
        function refresh() {
            var font = Number(size.value) / 841.8898 * preview.clientWidth;
            box.style.fontSize = font + 'px';
            box.style.width = width.value + '%';
            box.style.color = color.value;
            if (align) {
                box.style.height = Math.max(5 / 297 * preview.clientWidth, font * 1.2) + 'px';
                box.style.justifyContent = {L: 'flex-start', C: 'center', R: 'flex-end'}[align.value];
                var text = box.querySelector('span');
                text.style.transform = '';
                text.style.transformOrigin = {L: 'left top', C: 'center top', R: 'right top'}[align.value];
                text.style.transform = 'scaleX(' + Math.min(1, box.clientWidth / Math.max(1, text.offsetWidth)) + ')';
            }
            // Round down at the bottom/right edge so persisted coordinates stay inside.
            var maxX = Math.max(0, 100 - Number(width.value));
            var maxY = Math.max(0, 100 - box.offsetHeight / preview.clientHeight * 100);
            x.value = Math.min(Math.max(0, Number(x.value)), Math.floor(maxX * 1000) / 1000).toFixed(3);
            y.value = Math.min(Math.max(0, Number(y.value)), Math.floor(maxY * 1000) / 1000).toFixed(3);
            box.style.left = x.value + '%'; box.style.top = y.value + '%';
        }
        box.style.touchAction = 'none';
        box.addEventListener('pointerdown', function (event) {
            if (event.button !== 0) return;
            event.preventDefault(); dragging = true; box.setPointerCapture(event.pointerId);
            var rect = box.getBoundingClientRect(); dx = event.clientX - rect.left; dy = event.clientY - rect.top;
            box.classList.add('dragging');
        });
        box.addEventListener('pointermove', function (event) {
            if (!dragging) return;
            var rect = preview.getBoundingClientRect();
            x.value = (event.clientX - rect.left - dx) / preview.clientWidth * 100;
            y.value = (event.clientY - rect.top - dy) / preview.clientHeight * 100;
            refresh();
        });
        function stop() { dragging = false; box.classList.remove('dragging'); }
        ['pointerup', 'pointercancel', 'lostpointercapture'].forEach(function (name) { box.addEventListener(name, stop); });
        [x, y, width, size, color, align].filter(Boolean).forEach(function (input) { input.addEventListener('input', refresh); });
        window.addEventListener('resize', refresh);
        refresh();
    }
    bind(field('name-box'), field('name_x'), field('name_y'), field('name_width'), field('font_size'), field('font_color'));
    bind(field('number-box'), field('number_x'), field('number_y'), field('number_width'), field('number_font_size'), field('number_font_color'), field('number_align'));
    var background = document.querySelector('input[name=background]'), objectUrl;
    background.addEventListener('change', function () {
        if (!this.files[0]) return;
        if (objectUrl) URL.revokeObjectURL(objectUrl);
        objectUrl = URL.createObjectURL(this.files[0]); preview.style.backgroundImage = 'url(' + objectUrl + ')';
    });
}());
