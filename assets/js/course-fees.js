(function () {
    'use strict';
    var mode = document.getElementById('fee_mode');
    if (!mode) return;
    var rows = document.getElementById('fee-options-rows');
    function refresh() {
        var conditional = mode.value === 'options';
        document.getElementById('fee-options-panel').hidden = !conditional;
        document.getElementById('fixed-fee-field').hidden = conditional;
        document.getElementById('fee').required = !conditional;
        rows.querySelectorAll('.fee-option-row').forEach(function (row, index) {
            row.querySelectorAll('[data-field]').forEach(function (input) {
                input.name = 'fee_options[' + index + '][' + input.dataset.field + ']';
                input.required = conditional && (input.dataset.field === 'label' || input.dataset.field === 'amount');
            });
        });
    }
    document.getElementById('add-fee-option').addEventListener('click', function () {
        rows.appendChild(document.getElementById('fee-option-template').content.cloneNode(true));
        refresh();
    });
    rows.addEventListener('click', function (event) {
        var button = event.target.closest('button');
        if (!button) return;
        var row = button.closest('.fee-option-row');
        if (button.hasAttribute('data-remove') && row.dataset.saved === '0') row.remove();
        else if (button.dataset.move === 'up' && row.previousElementSibling) rows.insertBefore(row, row.previousElementSibling);
        else if (button.dataset.move === 'down' && row.nextElementSibling) rows.insertBefore(row.nextElementSibling, row);
        refresh();
    });
    mode.addEventListener('change', refresh);
    refresh();
}());
