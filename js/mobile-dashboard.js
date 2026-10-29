(function () {
    'use strict';

    var chart = document.getElementById('dailyChart');
    var table = document.getElementById('dailyTable');
    var buttons = document.querySelectorAll('[data-view]');
    var filterMenu = document.getElementById('dashboard-filter-menu');
    var filterToggle = filterMenu ? filterMenu.querySelector('summary') : null;

    if (filterMenu) {
        var closeButtons = filterMenu.querySelectorAll('.filter-close, .filter-backdrop');

        Array.prototype.forEach.call(closeButtons, function (button) {
            button.addEventListener('click', function () {
                filterMenu.open = false;
                filterToggle.focus();
            });
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && filterMenu.open) {
                filterMenu.open = false;
                filterToggle.focus();
            }
        });
    }

    Array.prototype.forEach.call(buttons, function (button) {
        button.addEventListener('click', function () {
            var showChart = button.getAttribute('data-view') === 'chart';

            chart.classList.toggle('is-hidden', !showChart);
            table.classList.toggle('is-hidden', showChart);

            Array.prototype.forEach.call(buttons, function (item) {
                item.classList.toggle('is-active', item === button);
            });
        });
    });
}());
