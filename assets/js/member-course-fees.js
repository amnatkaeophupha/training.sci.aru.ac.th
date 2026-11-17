(function () {
    'use strict';
    var money = new Intl.NumberFormat('th-TH', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.querySelectorAll('.js-course-fee-form').forEach(function (form) {
        var button = form.querySelector('[type=submit]');
        var reset = form.querySelector('[type=reset]');
        var preview = form.querySelector('[data-fee-preview]');
        function refresh() {
            var selected = form.querySelector('input[name=fee_option_id]:checked');
            var changed = selected && selected.value !== form.dataset.currentOption;
            button.disabled = !changed;
            reset.hidden = !changed;
            preview.hidden = !changed;
            if (!changed) return;
            var unit = Number(selected.dataset.amount);
            var people = Number(form.dataset.people);
            form.querySelector('[data-fee-total]').textContent = money.format(unit * people / 100) + ' บาท';
            form.querySelector('[data-fee-calculation]').textContent = money.format(unit / 100) + ' บาท × ' + people + ' คน';
        }
        form.addEventListener('change', refresh);
        form.addEventListener('reset', function () { window.requestAnimationFrame(refresh); });
        refresh();
    });
}());
