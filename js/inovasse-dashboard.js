(function () {
    'use strict';

    var sidebar = document.getElementById('dashboard-sidebar');
    var toggle = document.querySelector('[data-sidebar-toggle]');
    var overlay = document.querySelector('.sidebar-overlay');
    var selectedView = document.getElementById('selected-view');
    var dateFilter = document.getElementById('date-filter');
    var dateInputs = dateFilter.querySelectorAll('input[type="date"]');
    var options = document.querySelectorAll('.query-option');
    var closeButtons = document.querySelectorAll('[data-sidebar-close]');
    var exportButton = document.querySelector('[data-export-table]');
    var themeToggle = document.getElementById('theme-toggle');
    var themeLabel = document.querySelector('.theme-switch-label');
    var themeIcon = document.querySelector('.theme-switch-icon');
    var themeStorageKey = 'inovasse-dashboard-theme';

    function setTheme(isDark, savePreference) {
        var theme = isDark ? 'dark' : 'light';

        document.body.setAttribute('data-theme', theme);
        themeToggle.checked = isDark;
        themeToggle.setAttribute('aria-label', isDark ? 'Desabilitar modo escuro' : 'Habilitar modo escuro');
        themeLabel.textContent = isDark ? 'Modo escuro' : 'Modo claro';
        themeIcon.classList.toggle('fa-moon', isDark);
        themeIcon.classList.toggle('fa-sun', !isDark);

        if (savePreference) {
            try {
                window.localStorage.setItem(themeStorageKey, theme);
            } catch (error) {
                console.warn('Não foi possível salvar a preferência de tema.', error);
            }
        }
    }

    try {
        var savedTheme = window.localStorage.getItem(themeStorageKey);
        setTheme(savedTheme !== 'light', false);
    } catch (error) {
        setTheme(true, false);
        console.warn('Não foi possível carregar a preferência de tema.', error);
    }

    function setSidebarOpen(isOpen) {
        sidebar.classList.toggle('is-open', isOpen);
        overlay.classList.toggle('is-visible', isOpen);
        toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    }

    function selectView(button) {
        var view = button.getAttribute('data-view');
        var isFaturamento = view === 'faturamento';

        selectedView.value = view;
        dateFilter.hidden = !isFaturamento;
        Array.prototype.forEach.call(dateInputs, function (input) {
            input.disabled = !isFaturamento;
            input.required = isFaturamento;
        });
        Array.prototype.forEach.call(options, function (option) {
            var isActive = option === button;
            option.classList.toggle('is-active', isActive);
            option.setAttribute('aria-pressed', isActive ? 'true' : 'false');
        });
    }

    toggle.addEventListener('click', function () {
        setSidebarOpen(!sidebar.classList.contains('is-open'));
    });

    Array.prototype.forEach.call(closeButtons, function (button) {
        button.addEventListener('click', function () {
            setSidebarOpen(false);
        });
    });

    Array.prototype.forEach.call(options, function (button) {
        button.addEventListener('click', function () {
            selectView(button);
        });
    });

    themeToggle.addEventListener('change', function () {
        setTheme(themeToggle.checked, true);
    });

    if (exportButton) {
        exportButton.addEventListener('click', function () {
            var table = document.getElementById('query-results');
            var rows = table.querySelectorAll('tr');
            var csvRows = Array.prototype.map.call(rows, function (row) {
                var cells = row.querySelectorAll('th, td');
                return Array.prototype.map.call(cells, function (cell) {
                    return cell.textContent.trim().replace(/\s+/g, ' ').replace(/"/g, '');
                });
            });
            var operationTotals = document.querySelectorAll('.operation-summary-item');

            if (operationTotals.length > 0) {
                csvRows.push([]);
                Array.prototype.forEach.call(operationTotals, function (item) {
                    csvRows.push([
                        item.getAttribute('data-operation-name').replace(/"/g, ''),
                        item.getAttribute('data-operation-quantity').replace(/"/g, ''),
                        item.getAttribute('data-operation-value').replace(/"/g, '')
                    ]);
                });
            }

            var csv = csvRows.map(function (row) {
                return row.join(';');
            }).join('\r\n');
            var blob = new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8' });
            var link = document.createElement('a');
            var fileName = exportButton.getAttribute('data-export-title')
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-|-$/g, '');
            var objectUrl = URL.createObjectURL(blob);

            link.href = objectUrl;
            link.download = fileName + '-' + new Date().toISOString().slice(0, 10) + '.csv';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(objectUrl);
        });
    }

    selectView(document.querySelector('.query-option.is-active') || options[0]);
}());
