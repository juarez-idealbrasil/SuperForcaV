(function () {
    'use strict';

    var app = document.getElementById('order-app');
    var form = document.getElementById('order-form');
    if (!app || !form) {
        return;
    }

    var customerSearch = document.getElementById('customer-search');
    var customerResults = document.getElementById('customer-results');
    var representativeSelect = document.getElementById('representative-select');
    var selectedCustomer = document.getElementById('selected-customer');
    var customerIdInput = document.getElementById('customer-id');
    var productSearch = document.getElementById('product-search');
    var productResults = document.getElementById('product-results');
    var orderItems = document.getElementById('order-items');
    var itemsInput = document.getElementById('order-items-json');
    var conditionSelect = document.getElementById('condition-select');
    var paymentMethodSelect = document.getElementById('payment-method-select');
    var conditionIdInput = document.getElementById('condition-id');
    var paymentMethodIdInput = document.getElementById('payment-method-id');
    var saveButton = document.getElementById('save-order');
    var printOrderButton = document.getElementById('print-order');
    var message = document.getElementById('order-message');
    var saveConfirmationDialog = document.getElementById('order-confirmation');
    var saveConfirmationMessage = document.getElementById('order-confirmation-message');
    var deliveryDateValueInput = document.getElementById('delivery-date-value');
    var deliveryDatePicker = document.getElementById('delivery-date-picker');
    var deliveryDateCalendar = document.getElementById('delivery-date-calendar');
    var deliveryDateToggle = document.getElementById('delivery-date-toggle');
    var deliveryCalendarMonthLabel = document.getElementById('delivery-calendar-month');
    var deliveryCalendarGrid = document.getElementById('delivery-calendar-grid');
    var deliveryCalendarPrevious = document.getElementById('delivery-calendar-previous');
    var deliveryCalendarNext = document.getElementById('delivery-calendar-next');
    var countSummaries = document.querySelectorAll('[data-order-summary-count]');
    var totalSummaries = document.querySelectorAll('[data-order-summary-total]');
    var stepPanels = [
        document.getElementById('step-panel-one'),
        document.getElementById('step-panel-two'),
        document.getElementById('step-panel-three')
    ];
    var stepIndicators = [
        document.getElementById('order-step-one'),
        document.getElementById('order-step-two'),
        document.getElementById('order-step-three')
    ];
    var deliveryDateInput = document.getElementById('delivery-date');
    var observationInputs = [
        document.getElementById('observ-1'),
        document.getElementById('observ-2'),
        document.getElementById('observ-3')
    ];
    var items = [];
    var customer = null;
    var discountFieldEnabled = app.dataset.discountEnabled === '1';
    var currentStep = 1;
    var isSaving = false;
    var isSaved = false;
    var customerTimer = null;
    var productTimer = null;
    var customerRequest = 0;
    var productRequest = 0;
    var productImagePreview = null;
    var productImagePreviewImage = null;
    var productImagePreviewTrigger = null;
    var savedOrderId = null;
    var isPreparingOrderPdf = false;
    var orderPdfUrl = null;
    var orderPdfViewer = null;
    var orderPdfFrame = null;
    var orderPdfViewerTrigger = null;
    var selectedDeliveryDate = new Date(deliveryDateInput.value.split('/').reverse().join('-') + 'T00:00:00');
    var calendarMonth = new Date(selectedDeliveryDate.getFullYear(), selectedDeliveryDate.getMonth(), 1);

    function currency(value) {
        return new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        }).format(value);
    }

    function formatQuantity(value, useGrouping) {
        return new Intl.NumberFormat('pt-BR', {
            maximumFractionDigits: 3,
            useGrouping: Boolean(useGrouping)
        }).format(value);
    }

    function addPdfLines(lines, text, options) {
        var settings = options || {};
        var maxLength = settings.maxLength || 78;
        var words = String(text).split(/\s+/);
        var line = '';

        words.forEach(function (word) {
            while (word.length > maxLength) {
                if (line) {
                    lines.push({text: line, size: settings.size || 10, bold: Boolean(settings.bold)});
                    line = '';
                }
                lines.push({
                    text: word.slice(0, maxLength),
                    size: settings.size || 10,
                    bold: Boolean(settings.bold)
                });
                word = word.slice(maxLength);
            }

            if (line && (line.length + word.length + 1) > maxLength) {
                lines.push({text: line, size: settings.size || 10, bold: Boolean(settings.bold)});
                line = word;
            } else {
                line = line ? line + ' ' + word : word;
            }
        });

        if (line) {
            lines.push({text: line, size: settings.size || 10, bold: Boolean(settings.bold)});
        }
    }

    function encodePdfText(text) {
        var specialCharacters = {
            0x20ac: 0x80, 0x201a: 0x82, 0x192: 0x83, 0x201e: 0x84,
            0x2026: 0x85, 0x2020: 0x86, 0x2021: 0x87, 0x2c6: 0x88,
            0x2030: 0x89, 0x160: 0x8a, 0x2039: 0x8b, 0x152: 0x8c,
            0x17d: 0x8e, 0x2018: 0x91, 0x2019: 0x92, 0x201c: 0x93,
            0x201d: 0x94, 0x2022: 0x95, 0x2013: 0x96, 0x2014: 0x97,
            0x2dc: 0x98, 0x2122: 0x99, 0x161: 0x9a, 0x203a: 0x9b,
            0x153: 0x9c, 0x17e: 0x9e, 0x178: 0x9f
        };
        var hex = '';

        Array.from(String(text)).forEach(function (character) {
            var code = character.codePointAt(0);
            var byte = code <= 0x7e || (code >= 0xa0 && code <= 0xff)
                ? code
                : specialCharacters[code] || 0x3f;
            hex += byte.toString(16).padStart(2, '0');
        });

        return hex.toUpperCase();
    }

    function createOrderPdf(orderId) {
        var lines = [];
        var orderTotal = items.reduce(function (sum, item) {
            return sum + Math.round(item.price * item.quantity * (100 - item.discount)) / 100;
        }, 0);
        var today = new Intl.DateTimeFormat('pt-BR').format(new Date());
        var deliveryDate = new Intl.DateTimeFormat('pt-BR', {timeZone: 'UTC'}).format(
            new Date(deliveryDateValueInput.value + 'T00:00:00Z')
        );

        addPdfLines(lines, 'PEDIDO DE VENDA #' + orderId, {bold: true, size: 18});
        addPdfLines(lines, 'Forca V - Pedido pendente', {bold: true, size: 11});
        lines.push({text: 'Emitido em: ' + today, size: 10});
        lines.push({text: '', size: 6});
        addPdfLines(lines, 'Cliente: ' + customer.name, {bold: true});
        if (customer.document) {
            addPdfLines(lines, 'Documento: ' + customer.document);
        }
        addPdfLines(lines, 'Condicao de pagamento: ' + conditionSelect.options[conditionSelect.selectedIndex].text);
        addPdfLines(lines, 'Forma de pagamento: ' + paymentMethodSelect.options[paymentMethodSelect.selectedIndex].text);
        addPdfLines(lines, 'Data de entrega: ' + deliveryDate);
        lines.push({text: '', size: 6});
        addPdfLines(lines, 'PRODUTOS', {bold: true, size: 12});
        addPdfLines(lines, '------------------------------------------------------------');

        items.forEach(function (item, index) {
            var lineTotal = Math.round(item.price * item.quantity * (100 - item.discount)) / 100;
            addPdfLines(lines, (index + 1) + '. ' + item.description, {bold: true});
            addPdfLines(
                lines,
                'Qtd.: ' + formatQuantity(item.quantity, true)
                    + ' | Unitario: ' + currency(item.price)
                    + ' | Desconto: ' + formatQuantity(item.discount, true) + '%'
            );
            addPdfLines(lines, 'Total do item: ' + currency(lineTotal), {bold: true});
            lines.push({text: '', size: 5});
        });

        addPdfLines(lines, 'TOTAL DO PEDIDO: ' + currency(orderTotal), {bold: true, size: 13});
        observationInputs.forEach(function (input, index) {
            if (input.value.trim()) {
                addPdfLines(lines, 'Observacao ' + (index + 1) + ': ' + input.value.trim());
            }
        });

        var pageLines = [];
        while (lines.length) {
            pageLines.push(lines.splice(0, 43));
        }

        var objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '',
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>',
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>'
        ];
        var pageReferences = [];

        pageLines.forEach(function (page) {
            var pageObjectNumber = objects.length + 1;
            var contentObjectNumber = pageObjectNumber + 1;
            var content = page.map(function (line, index) {
                if (!line.text) {
                    return '';
                }
                var font = line.bold ? 'F2' : 'F1';
                var y = 790 - index * 17;
                return 'BT /' + font + ' ' + line.size + ' Tf 48 ' + y
                    + ' Td <' + encodePdfText(line.text) + '> Tj ET\n';
            }).join('');

            pageReferences.push(pageObjectNumber + ' 0 R');
            objects.push(
                '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842]'
                    + ' /Resources << /Font << /F1 3 0 R /F2 4 0 R >> >>'
                    + ' /Contents ' + contentObjectNumber + ' 0 R >>'
            );
            objects.push('<< /Length ' + content.length + ' >>\nstream\n' + content + 'endstream');
        });

        objects[1] = '<< /Type /Pages /Kids [' + pageReferences.join(' ')
            + '] /Count ' + pageReferences.length + ' >>';

        var pdf = '%PDF-1.4\n';
        var offsets = [0];
        objects.forEach(function (object, index) {
            offsets.push(pdf.length);
            pdf += (index + 1) + ' 0 obj\n' + object + '\nendobj\n';
        });

        var xrefOffset = pdf.length;
        pdf += 'xref\n0 ' + (objects.length + 1) + '\n';
        pdf += '0000000000 65535 f \n';
        for (var objectIndex = 1; objectIndex < offsets.length; objectIndex += 1) {
            pdf += String(offsets[objectIndex]).padStart(10, '0') + ' 00000 n \n';
        }
        pdf += 'trailer\n<< /Size ' + (objects.length + 1) + ' /Root 1 0 R >>\n';
        pdf += 'startxref\n' + xrefOffset + '\n%%EOF';

        return new Blob([pdf], {type: 'application/pdf'});
    }

    function parseDeliveryDate(value) {
        var match = /^(\d{2})\/(\d{2})\/(\d{4})$/.exec(value);
        if (!match) {
            return '';
        }

        var day = Number(match[1]);
        var month = Number(match[2]);
        var year = Number(match[3]);
        var date = new Date(year, month - 1, day);
        if (date.getFullYear() !== year || date.getMonth() !== month - 1 || date.getDate() !== day) {
            return '';
        }

        return year + '-' + match[2] + '-' + match[1];
    }

    function formatDateForInput(date) {
        return [
            String(date.getDate()).padStart(2, '0'),
            String(date.getMonth() + 1).padStart(2, '0'),
            date.getFullYear()
        ].join('/');
    }

    function formatDateForSubmission(date) {
        return [
            date.getFullYear(),
            String(date.getMonth() + 1).padStart(2, '0'),
            String(date.getDate()).padStart(2, '0')
        ].join('-');
    }

    function setDeliveryCalendarOpen(isOpen) {
        deliveryDateCalendar.hidden = !isOpen;
        deliveryDateToggle.setAttribute('aria-expanded', String(isOpen));
        deliveryDateInput.setAttribute('aria-expanded', String(isOpen));
    }

    function renderDeliveryCalendar() {
        var minDateParts = deliveryDateInput.dataset.minDate.split('-');
        var minDate = new Date(
            Number(minDateParts[0]),
            Number(minDateParts[1]) - 1,
            Number(minDateParts[2])
        );
        var minMonth = new Date(minDate.getFullYear(), minDate.getMonth(), 1);
        if (calendarMonth < minMonth) {
            calendarMonth = minMonth;
        }
        deliveryCalendarMonthLabel.textContent = new Intl.DateTimeFormat('pt-BR', {
            month: 'long',
            year: 'numeric'
        }).format(calendarMonth);
        deliveryCalendarPrevious.disabled = calendarMonth <= minMonth;
        deliveryCalendarGrid.textContent = '';

        ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'].forEach(function (weekday) {
            var heading = document.createElement('span');
            heading.className = 'delivery-calendar-weekday';
            heading.textContent = weekday;
            heading.setAttribute('aria-hidden', 'true');
            deliveryCalendarGrid.appendChild(heading);
        });

        var firstWeekday = calendarMonth.getDay();
        for (var blank = 0; blank < firstWeekday; blank += 1) {
            var emptyCell = document.createElement('span');
            emptyCell.setAttribute('aria-hidden', 'true');
            deliveryCalendarGrid.appendChild(emptyCell);
        }

        var daysInMonth = new Date(
            calendarMonth.getFullYear(),
            calendarMonth.getMonth() + 1,
            0
        ).getDate();
        for (var day = 1; day <= daysInMonth; day += 1) {
            var date = new Date(calendarMonth.getFullYear(), calendarMonth.getMonth(), day);
            var dateValue = formatDateForSubmission(date);
            var dayButton = document.createElement('button');
            dayButton.type = 'button';
            dayButton.className = 'delivery-calendar-day';
            dayButton.textContent = String(day);
            dayButton.disabled = dateValue < deliveryDateInput.dataset.minDate;
            dayButton.setAttribute('aria-label', new Intl.DateTimeFormat('pt-BR', {
                dateStyle: 'full'
            }).format(date));
            dayButton.setAttribute('aria-pressed', String(dateValue === deliveryDateValueInput.value));
            dayButton.addEventListener('click', function (selectedDate) {
                return function () {
                    deliveryDateInput.value = formatDateForInput(selectedDate);
                    deliveryDateValueInput.value = formatDateForSubmission(selectedDate);
                    updateSaveButton();
                    setDeliveryCalendarOpen(false);
                    deliveryDateToggle.focus();
                };
            }(date));
            deliveryCalendarGrid.appendChild(dayButton);
        }
    }

    function showMessage(text, type) {
        message.textContent = text;
        message.className = 'order-message is-' + type;
        message.hidden = false;
    }

    function confirmSaveOrder(text) {
        saveConfirmationMessage.textContent = text;
        return new Promise(function (resolve) {
            saveConfirmationDialog.addEventListener('close', function () {
                resolve(saveConfirmationDialog.returnValue === 'save');
            }, {once: true});
            saveConfirmationDialog.showModal();
        });
    }

    function showOrderPdfLink(text, pdfBlob, orderId) {
        if (orderPdfUrl) {
            URL.revokeObjectURL(orderPdfUrl);
        }
        orderPdfUrl = URL.createObjectURL(pdfBlob);

        message.textContent = '';
        message.className = 'order-message is-success';
        message.appendChild(document.createTextNode(text + ' '));

        var link = document.createElement('a');
        link.href = orderPdfUrl;
        link.type = 'application/pdf';
        link.textContent = 'Visualizar PDF';
        link.setAttribute('aria-label', 'Visualizar o PDF do pedido ' + orderId + ' nesta tela');
        link.addEventListener('click', function (event) {
            event.preventDefault();
            openOrderPdfViewer(link);
        });
        message.appendChild(link);
        message.hidden = false;
    }

    function openOrderPdfViewer(trigger) {
        if (!orderPdfUrl) {
            return;
        }

        if (!orderPdfViewer) {
            orderPdfViewer = document.createElement('section');
            orderPdfViewer.className = 'order-pdf-viewer';
            orderPdfViewer.setAttribute('role', 'dialog');
            orderPdfViewer.setAttribute('aria-modal', 'true');
            orderPdfViewer.setAttribute('aria-label', 'Visualização do pedido em PDF');
            orderPdfViewer.hidden = true;

            var toolbar = document.createElement('div');
            toolbar.className = 'order-pdf-viewer-toolbar';

            var title = document.createElement('strong');
            title.textContent = 'Pedido em PDF';

            var closeButton = document.createElement('button');
            closeButton.type = 'button';
            closeButton.className = 'order-pdf-viewer-close';
            closeButton.textContent = 'Fechar';
            closeButton.addEventListener('click', closeOrderPdfViewer);

            orderPdfFrame = document.createElement('iframe');
            orderPdfFrame.className = 'order-pdf-viewer-frame';
            orderPdfFrame.title = 'Documento PDF do pedido';

            toolbar.appendChild(title);
            toolbar.appendChild(closeButton);
            orderPdfViewer.appendChild(toolbar);
            orderPdfViewer.appendChild(orderPdfFrame);
            orderPdfViewer.addEventListener('click', function (event) {
                if (event.target === orderPdfViewer) {
                    closeOrderPdfViewer();
                }
            });
            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && !orderPdfViewer.hidden) {
                    closeOrderPdfViewer();
                }
            });
            document.body.appendChild(orderPdfViewer);
        }

        orderPdfViewerTrigger = trigger;
        orderPdfFrame.src = orderPdfUrl;
        orderPdfViewer.hidden = false;
        document.body.classList.add('order-pdf-viewer-open');
        orderPdfViewer.querySelector('.order-pdf-viewer-close').focus();
    }

    function closeOrderPdfViewer() {
        if (!orderPdfViewer || orderPdfViewer.hidden) {
            return;
        }

        orderPdfViewer.hidden = true;
        orderPdfFrame.removeAttribute('src');
        document.body.classList.remove('order-pdf-viewer-open');
        if (orderPdfViewerTrigger) {
            orderPdfViewerTrigger.focus();
            orderPdfViewerTrigger = null;
        }
    }

    function hideMessage() {
        message.textContent = '';
        message.hidden = true;
        message.className = 'order-message';
    }

    function hasPaymentSelections() {
        return Boolean(conditionSelect.value && paymentMethodSelect.value);
    }

    function updateProductSearchAccess() {
        var canSearchProducts = Boolean(customer && hasPaymentSelections());
        productSearch.disabled = !canSearchProducts;
        productSearch.placeholder = !customer
            ? 'Selecione um cliente primeiro'
            : (canSearchProducts
                ? 'Descrição ou código de barras'
                : 'Informe condição e forma de pagamento');

        if (!canSearchProducts) {
            window.clearTimeout(productTimer);
            productRequest += 1;
            productSearch.value = '';
            productResults.hidden = true;
            productResults.textContent = '';
        }
    }

    function setCurrentStep(step) {
        currentStep = step;
        stepPanels.forEach(function (panel, index) {
            panel.hidden = index + 1 !== step;
        });
        stepIndicators.forEach(function (indicator, index) {
            var stepNumber = index + 1;
            indicator.classList.toggle('is-current', stepNumber === step);
            indicator.classList.toggle('is-complete', stepNumber < step);
            if (stepNumber === step) {
                indicator.setAttribute('aria-current', 'step');
            } else {
                indicator.removeAttribute('aria-current');
            }
        });
        updateSaveButton();
        window.scrollTo(0, 0);
    }

    function goToStep(step) {
        if (step > currentStep && step >= 2 && !customer) {
            showMessage('Selecione um cliente antes de continuar.', 'error');
            customerSearch.focus();
            return;
        }
        if (step > currentStep && step >= 2 && !hasPaymentSelections()) {
            showMessage('Selecione a condição e a forma de pagamento antes de continuar.', 'error');
            if (!conditionSelect.value) {
                conditionSelect.focus();
            } else {
                paymentMethodSelect.focus();
            }
            return;
        }
        if (step > currentStep && step >= 3 && items.length === 0) {
            showMessage('Adicione ao menos um produto antes de continuar.', 'error');
            productSearch.focus();
            return;
        }

        hideMessage();
        setCurrentStep(step);
    }

    function closeProductImagePreview() {
        if (!productImagePreview || productImagePreview.hidden) {
            return;
        }

        productImagePreview.hidden = true;
        productImagePreviewImage.removeAttribute('src');
        if (productImagePreviewTrigger) {
            productImagePreviewTrigger.focus();
            productImagePreviewTrigger = null;
        }
    }

    function openProductImagePreview(image, label) {
        if (image.classList.contains('is-unavailable') || !image.src) {
            return;
        }

        if (!productImagePreview) {
            productImagePreview = document.createElement('div');
            productImagePreview.className = 'product-image-preview';
            productImagePreview.setAttribute('role', 'dialog');
            productImagePreview.setAttribute('aria-modal', 'true');
            productImagePreview.setAttribute('aria-label', 'Imagem ampliada do produto');
            productImagePreview.hidden = true;

            var closeButton = document.createElement('button');
            closeButton.type = 'button';
            closeButton.className = 'product-image-preview-close';
            closeButton.textContent = 'Fechar';
            closeButton.setAttribute('aria-label', 'Fechar imagem ampliada');
            closeButton.addEventListener('click', closeProductImagePreview);

            productImagePreviewImage = document.createElement('img');
            productImagePreviewImage.className = 'product-image-preview-image';
            productImagePreviewImage.alt = '';

            productImagePreview.appendChild(closeButton);
            productImagePreview.appendChild(productImagePreviewImage);
            productImagePreview.addEventListener('click', function (event) {
                if (event.target === productImagePreview) {
                    closeProductImagePreview();
                }
            });
            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    closeProductImagePreview();
                }
            });
            document.body.appendChild(productImagePreview);
        }

        productImagePreviewImage.src = image.currentSrc || image.src;
        productImagePreviewImage.alt = label;
        productImagePreviewTrigger = image;
        productImagePreview.hidden = false;
        productImagePreview.querySelector('.product-image-preview-close').focus();
    }

    function makeSearchResult(label, detail, onClick, buttonLabel, imageData, buttonAriaLabel, stock) {
        var row = document.createElement('div');
        row.className = 'order-search-result';

        if (imageData) {
            var image = document.createElement('img');
            image.className = 'order-result-image';
            image.src = imageData.url;
            image.alt = label;
            image.setAttribute('role', 'button');
            image.setAttribute('tabindex', '0');
            image.setAttribute('aria-label', 'Ampliar imagem do produto: ' + label);
            image.title = 'Segure por 1 segundo para ampliar';
            image.loading = 'lazy';

            var pressTimer = null;
            var pressStartX = 0;
            var pressStartY = 0;
            function clearPressTimer() {
                if (pressTimer !== null) {
                    window.clearTimeout(pressTimer);
                    pressTimer = null;
                }
            }

            image.addEventListener('pointerdown', function (event) {
                if (event.button !== undefined && event.button !== 0) {
                    return;
                }
                pressStartX = event.clientX;
                pressStartY = event.clientY;
                pressTimer = window.setTimeout(function () {
                    pressTimer = null;
                    openProductImagePreview(image, label);
                }, 1000);
            });
            image.addEventListener('pointermove', function (event) {
                if (Math.abs(event.clientX - pressStartX) > 10
                    || Math.abs(event.clientY - pressStartY) > 10) {
                    clearPressTimer();
                }
            });
            image.addEventListener('pointerup', clearPressTimer);
            image.addEventListener('pointercancel', clearPressTimer);
            image.addEventListener('pointerleave', clearPressTimer);
            image.addEventListener('contextmenu', function (event) {
                event.preventDefault();
            });
            image.addEventListener('keydown', function (event) {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    openProductImagePreview(image, label);
                }
            });
            image.addEventListener('error', function () {
                if (imageData.fallbackUrl && image.src !== imageData.fallbackUrl) {
                    image.src = imageData.fallbackUrl;
                    imageData.fallbackUrl = '';
                    return;
                }
                image.classList.add('is-unavailable');
                image.removeAttribute('src');
            });
            row.appendChild(image);
        }

        var copy = document.createElement('span');
        copy.className = 'order-result-copy';
        var title = document.createElement('strong');
        title.textContent = label;
        copy.appendChild(title);
        if (detail) {
            var description = document.createElement('small');
            description.textContent = detail;
            copy.appendChild(description);
        }
        if (stock) {
            var stockLabel = document.createElement('small');
            stockLabel.className = 'order-result-stock';
            stockLabel.textContent = stock;
            copy.appendChild(stockLabel);
        }

        var button = document.createElement('button');
        button.type = 'button';
        button.className = 'order-result-button';
        button.textContent = buttonLabel;
        if (buttonAriaLabel) {
            button.setAttribute('aria-label', buttonAriaLabel);
        }
        button.addEventListener('click', onClick);

        row.appendChild(copy);
        row.appendChild(button);
        return row;
    }

    function clearCustomer() {
        customer = null;
        customerIdInput.value = '';
        selectedCustomer.hidden = true;
        selectedCustomer.textContent = '';
        conditionSelect.disabled = true;
        paymentMethodSelect.disabled = true;
        conditionSelect.innerHTML = '<option value="">Selecione um cliente</option>';
        paymentMethodSelect.innerHTML = '<option value="">Selecione um cliente</option>';
        updateProductSearchAccess();
        conditionIdInput.value = '';
        paymentMethodIdInput.value = '';
        items = [];
        renderItems();
        updateSaveButton();
    }

    function selectCustomer(result) {
        hideMessage();
        customer = result;
        customerIdInput.value = String(result.id);
        customerSearch.value = result.name;
        customerResults.hidden = true;
        customerResults.textContent = '';

        selectedCustomer.textContent = '';
        var name = document.createElement('strong');
        name.textContent = result.name;
        var details = document.createElement('span');
        var customerDetails = [result.document, result.alias, 'Representante: ' + result.representative_name]
            .filter(function (value) { return Boolean(value); });
        details.textContent = customerDetails.join(' · ');
        selectedCustomer.appendChild(name);
        selectedCustomer.appendChild(details);
        selectedCustomer.hidden = false;

        conditionSelect.disabled = true;
        paymentMethodSelect.disabled = true;
        conditionSelect.innerHTML = '<option value="">Carregando condições...</option>';
        paymentMethodSelect.innerHTML = '<option value="">Carregando formas...</option>';
        updateProductSearchAccess();
        items = [];
        renderItems();
        loadPaymentOptions(result.id);
        updateSaveButton();
    }

    function renderCustomerResults(data) {
        customerResults.textContent = '';
        if (!data.items || data.items.length === 0) {
            var empty = document.createElement('p');
            empty.className = 'empty-order';
            empty.textContent = 'Nenhum cliente encontrado.';
            customerResults.appendChild(empty);
        } else {
            data.items.forEach(function (result) {
                var detail = [result.document, result.alias, 'Representante: ' + result.representative_name]
                    .filter(function (value) { return Boolean(value); })
                    .join(' · ');
                customerResults.appendChild(makeSearchResult(
                    result.name,
                    detail,
                    function () { selectCustomer(result); },
                    'Selecionar'
                ));
            });
        }
        customerResults.hidden = false;
    }

    function searchCustomers(term) {
        var requestId = ++customerRequest;
        var url = app.dataset.customersUrl + '?q=' + encodeURIComponent(term);
        if (representativeSelect.value) {
            url += '&representante=' + encodeURIComponent(representativeSelect.value);
        }
        fetch(url, {
            credentials: 'same-origin',
            headers: {'Accept': 'application/json'}
        })
            .then(function (response) {
                return response.json().then(function (data) {
                    if (!response.ok) {
                        throw new Error(data.message || 'Não foi possível buscar os clientes.');
                    }
                    return data;
                });
            })
            .then(function (data) {
                if (requestId === customerRequest) {
                    renderCustomerResults(data);
                }
            })
            .catch(function (error) {
                if (requestId === customerRequest) {
                    showMessage(error.message, 'error');
                }
            });
    }

    function loadRepresentatives() {
        fetch(app.dataset.representativesUrl, {
            credentials: 'same-origin',
            headers: {'Accept': 'application/json'}
        })
            .then(function (response) {
                return response.json().then(function (data) {
                    if (!response.ok) {
                        throw new Error(data.message || 'Não foi possível carregar os representantes.');
                    }
                    return data;
                });
            })
            .then(function (data) {
                if (!Array.isArray(data.items)) {
                    throw new Error('A lista de representantes retornada é inválida.');
                }
                representativeSelect.textContent = '';
                var allOption = document.createElement('option');
                allOption.value = '';
                allOption.textContent = 'Todos os representantes (opcional)';
                representativeSelect.appendChild(allOption);

                data.items.forEach(function (representative) {
                    var option = document.createElement('option');
                    option.value = String(representative.id);
                    option.textContent = representative.name;
                    representativeSelect.appendChild(option);
                });
                representativeSelect.disabled = false;
            })
            .catch(function (error) {
                representativeSelect.textContent = '';
                var fallbackOption = document.createElement('option');
                fallbackOption.value = '';
                fallbackOption.textContent = 'Filtro indisponível - buscar todos';
                representativeSelect.appendChild(fallbackOption);
                representativeSelect.disabled = false;
                showMessage(error.message, 'error');
            });
    }

    function renderProductResults(data) {
        productResults.textContent = '';
        if (!data.items || data.items.length === 0) {
            var empty = document.createElement('p');
            empty.className = 'empty-order';
            empty.textContent = 'Nenhum produto com preço encontrado para este cliente.';
            productResults.appendChild(empty);
        } else {
            data.items.forEach(function (product) {
                productResults.appendChild(makeSearchResult(
                    product.description,
                    'Cód. ' + (product.barcode || product.id) + ' · ' + currency(product.price),
                    function () { addProduct(product); },
                    '+',
                    {
                        url: product.image_url,
                        fallbackUrl: product.fallback_image_url
                    },
                    'Adicionar produto: ' + product.description,
                    'Estoque: ' + formatQuantity(Number(product.stock), true)
                ));
            });
        }
        productResults.hidden = false;
    }

    function searchProducts(term) {
        if (!customer) {
            return;
        }

        var requestId = ++productRequest;
        var url = app.dataset.productsUrl
            + '?cliente=' + encodeURIComponent(customer.id)
            + '&q=' + encodeURIComponent(term);
        fetch(url, {
            credentials: 'same-origin',
            headers: {'Accept': 'application/json'}
        })
            .then(function (response) {
                return response.json().then(function (data) {
                    if (!response.ok) {
                        throw new Error(data.message || 'Não foi possível buscar os produtos.');
                    }
                    return data;
                });
            })
            .then(function (data) {
                if (requestId === productRequest) {
                    renderProductResults(data);
                }
            })
            .catch(function (error) {
                if (requestId === productRequest) {
                    showMessage(error.message, 'error');
                }
            });
    }

    function addProduct(product) {
        if (!customer || !hasPaymentSelections()) {
            updateProductSearchAccess();
            showMessage('Informe a condição e a forma de pagamento antes de adicionar produtos.', 'error');
            return;
        }

        var existing = items.find(function (item) {
            return item.id === product.id;
        });
        if (existing) {
            if (existing.quantity + 1 <= 100000) {
                existing.quantity += 1;
            }
            existing.imageUrl = product.image_url || existing.imageUrl || '';
            existing.fallbackImageUrl = product.fallback_image_url || existing.fallbackImageUrl || '';
        } else {
            items.push({
                id: product.id,
                description: product.description,
                price: Number(product.price),
                quantity: 1,
                discount: 0,
                imageUrl: product.image_url || '',
                fallbackImageUrl: product.fallback_image_url || ''
            });
        }
        hideMessage();
        renderItems();
        productResults.hidden = true;
        productSearch.value = '';
        productSearch.focus();
    }

    function renderItems() {
        orderItems.textContent = '';
        if (items.length === 0) {
            var empty = document.createElement('p');
            empty.className = 'empty-order';
            empty.textContent = customer
                ? 'Busque e adicione produtos ao pedido.'
                : 'Selecione um cliente para buscar produtos.';
            orderItems.appendChild(empty);
        } else {
            items.forEach(function (item, index) {
                var article = document.createElement('article');
                article.className = 'mock-product';

                var fallbackSymbol = document.createElement('span');
                fallbackSymbol.className = 'product-symbol';
                fallbackSymbol.setAttribute('aria-hidden', 'true');
                fallbackSymbol.textContent = 'P';
                var symbol = fallbackSymbol;
                if (item.imageUrl) {
                    var thumbnail = document.createElement('img');
                    thumbnail.className = 'product-symbol product-thumbnail';
                    thumbnail.src = item.imageUrl;
                    thumbnail.alt = item.description;
                    thumbnail.loading = 'lazy';
                    var fallbackImageUrl = item.fallbackImageUrl;
                    thumbnail.addEventListener('error', function () {
                        if (fallbackImageUrl) {
                            thumbnail.src = fallbackImageUrl;
                            fallbackImageUrl = '';
                        } else {
                            thumbnail.replaceWith(fallbackSymbol);
                        }
                    });
                    symbol = thumbnail;
                }

                var details = document.createElement('div');
                details.className = 'product-details';
                var title = document.createElement('strong');
                title.textContent = item.description;
                var unitPrice = document.createElement('span');
                unitPrice.textContent = 'Preço do catálogo: ' + currency(item.price);
                var lineTotal = document.createElement('b');
                lineTotal.textContent = 'Total do item: ' + currency(
                    Math.round(item.price * item.quantity * (100 - item.discount)) / 100
                );
                details.appendChild(title);
                details.appendChild(unitPrice);
                details.appendChild(lineTotal);

                var quantity = document.createElement('div');
                quantity.className = 'quantity-control';
                var quantityLabel = document.createElement('label');
                quantityLabel.setAttribute('for', 'quantity-' + item.id);
                quantityLabel.textContent = 'Quantidade';

                var stepper = document.createElement('div');
                stepper.className = 'quantity-stepper';

                var decrease = document.createElement('button');
                decrease.type = 'button';
                decrease.className = 'quantity-decrease';
                decrease.textContent = '−';
                decrease.setAttribute('aria-label', 'Diminuir quantidade de ' + item.description);
                decrease.addEventListener('click', function () {
                    var newQuantity = Math.round((item.quantity - 1) * 1000) / 1000;
                    if (newQuantity <= 0) {
                        items.splice(index, 1);
                    } else {
                        items[index].quantity = newQuantity;
                    }
                    renderItems();
                });

                var quantityInput = document.createElement('input');
                quantityInput.type = 'text';
                quantityInput.inputMode = 'decimal';
                quantityInput.id = 'quantity-' + item.id;
                quantityInput.value = formatQuantity(item.quantity, false);
                quantityInput.setAttribute('aria-label', 'Quantidade de ' + item.description);
                quantityInput.addEventListener('change', function () {
                    var rawQuantity = quantityInput.value.trim();
                    if (!/^\d+(,\d{1,3})?$/.test(rawQuantity)) {
                        quantityInput.value = formatQuantity(item.quantity, false);
                        return;
                    }
                    var newQuantity = Number(rawQuantity.replace(',', '.'));
                    if (!Number.isFinite(newQuantity) || newQuantity <= 0 || newQuantity > 100000) {
                        quantityInput.value = formatQuantity(item.quantity, false);
                        return;
                    }
                    items[index].quantity = newQuantity;
                    renderItems();
                });

                var increase = document.createElement('button');
                increase.type = 'button';
                increase.className = 'quantity-increase';
                increase.textContent = '+';
                increase.setAttribute('aria-label', 'Aumentar quantidade de ' + item.description);
                increase.disabled = item.quantity + 1 > 100000;
                increase.addEventListener('click', function () {
                    if (item.quantity + 1 <= 100000) {
                        items[index].quantity = Math.round((item.quantity + 1) * 1000) / 1000;
                        renderItems();
                    }
                });

                quantity.appendChild(quantityLabel);
                stepper.appendChild(decrease);
                stepper.appendChild(quantityInput);
                stepper.appendChild(increase);
                quantity.appendChild(stepper);

                var controls = document.createElement('div');
                controls.className = 'product-controls';
                controls.appendChild(quantity);

                var discount = document.createElement('div');
                discount.className = 'discount-control';
                var discountLabel = document.createElement('label');
                discountLabel.setAttribute('for', 'discount-' + item.id);
                discountLabel.textContent = 'Desconto';
                var discountInput = document.createElement('input');
                discountInput.type = 'number';
                discountInput.id = 'discount-' + item.id;
                discountInput.min = '0';
                discountInput.max = '100';
                discountInput.step = '0.01';
                discountInput.value = String(item.discount);
                discountInput.disabled = !discountFieldEnabled;
                if (!discountFieldEnabled) {
                    discountInput.title = 'Desconto desabilitado pela configuração do sistema.';
                }
                discountInput.setAttribute('aria-label', 'Desconto percentual de ' + item.description);
                discountInput.addEventListener('change', function () {
                    var newDiscount = Number(discountInput.value);
                    if (!Number.isFinite(newDiscount) || newDiscount < 0 || newDiscount > 100) {
                        discountInput.value = String(item.discount);
                        return;
                    }
                    items[index].discount = Math.round(newDiscount * 100) / 100;
                    renderItems();
                });
                var percentSign = document.createElement('span');
                percentSign.textContent = '%';
                discount.appendChild(discountLabel);
                discount.appendChild(discountInput);
                discount.appendChild(percentSign);
                controls.appendChild(discount);

                article.appendChild(symbol);
                article.appendChild(details);
                article.appendChild(controls);
                orderItems.appendChild(article);
            });
        }

        var totalQuantity = items.reduce(function (total, item) {
            return total + item.quantity;
        }, 0);
        var total = items.reduce(function (sum, item) {
            return sum + Math.round(item.price * item.quantity * (100 - item.discount)) / 100;
        }, 0);
        countSummaries.forEach(function (summary) {
            summary.textContent = formatQuantity(totalQuantity, true)
                + ' itens · ' + items.length + ' produtos';
        });
        totalSummaries.forEach(function (summary) {
            summary.textContent = currency(total);
        });
        itemsInput.value = JSON.stringify(items.map(function (item) {
            return {
                id: item.id,
                quantity: item.quantity,
                price: item.price,
                discount: item.discount
            };
        }));
        updateSaveButton();
    }

    function setOptions(select, options, emptyLabel, labelBuilder) {
        select.textContent = '';
        var placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = emptyLabel;
        select.appendChild(placeholder);

        options.forEach(function (option) {
            var element = document.createElement('option');
            element.value = String(option.id);
            element.textContent = labelBuilder(option);
            select.appendChild(element);
        });
        select.disabled = options.length === 0;
    }

    function loadPaymentOptions(customerId) {
        fetch(app.dataset.paymentsUrl + '?cliente=' + encodeURIComponent(customerId), {
            credentials: 'same-origin',
            headers: {'Accept': 'application/json'}
        })
            .then(function (response) {
                return response.json().then(function (data) {
                    if (!response.ok) {
                        throw new Error(data.message || 'Não foi possível carregar as opções de pagamento.');
                    }
                    return data;
                });
            })
            .then(function (data) {
                if (!customer || Number(customer.id) !== Number(customerId)) {
                    return;
                }
                setOptions(
                    conditionSelect,
                    data.conditions,
                    'Selecione a condição',
                    function (condition) {
                        return condition.description + (condition.minimum_order > 0
                            ? ' · mínimo ' + currency(condition.minimum_order)
                            : '');
                    }
                );
                setOptions(
                    paymentMethodSelect,
                    data.payment_methods,
                    'Selecione a forma',
                    function (method) { return method.description;                     }
                );
                updateProductSearchAccess();
                if (!conditionSelect.disabled) {
                    conditionSelect.focus();
                } else if (!paymentMethodSelect.disabled) {
                    paymentMethodSelect.focus();
                }
                updateSaveButton();
                if (!data.conditions.length || !data.payment_methods.length) {
                    showMessage('Não há opções de pagamento válidas para este cliente. Verifique o cadastro comercial.', 'error');
                }
            })
            .catch(function (error) {
                showMessage(error.message, 'error');
            });
    }

    function updateSaveButton() {
        conditionIdInput.value = conditionSelect.value;
        paymentMethodIdInput.value = paymentMethodSelect.value;
        var deliveryDate = parseDeliveryDate(deliveryDateInput.value);
        deliveryDateValueInput.value = deliveryDate;
        saveButton.disabled = isSaving || isSaved || !customer || items.length === 0
            || !conditionSelect.value || !paymentMethodSelect.value
            || currentStep !== 3 || !deliveryDate
            || deliveryDate < deliveryDateInput.dataset.minDate
            || observationInputs.some(function (input) { return input.value.length > 80; });
    }

    customerSearch.addEventListener('input', function () {
        hideMessage();
        var term = customerSearch.value.trim();
        if (customer && term !== customer.name) {
            clearCustomer();
        }
        window.clearTimeout(customerTimer);
        if (term.length < 2) {
            customerResults.hidden = true;
            customerResults.textContent = '';
            return;
        }
        customerTimer = window.setTimeout(function () {
            searchCustomers(term);
        }, 250);
    });

    representativeSelect.addEventListener('change', function () {
        hideMessage();
        window.clearTimeout(customerTimer);
        customerRequest += 1;
        customerResults.hidden = true;
        customerResults.textContent = '';

        var hadSelectedCustomer = Boolean(customer);
        var term = hadSelectedCustomer ? '' : customerSearch.value.trim();
        if (hadSelectedCustomer) {
            customerSearch.value = '';
        }
        clearCustomer();

        if (term.length >= 2) {
            searchCustomers(term);
        }
    });

    productSearch.addEventListener('input', function () {
        if (productSearch.disabled || !hasPaymentSelections()) {
            updateProductSearchAccess();
            return;
        }
        hideMessage();
        var term = productSearch.value.trim();
        window.clearTimeout(productTimer);
        if (term.length < 2) {
            productResults.hidden = true;
            productResults.textContent = '';
            return;
        }
        productTimer = window.setTimeout(function () {
            searchProducts(term);
        }, 250);
    });

    conditionSelect.addEventListener('change', function () {
        updateProductSearchAccess();
        updateSaveButton();
    });
    paymentMethodSelect.addEventListener('change', function () {
        updateProductSearchAccess();
        updateSaveButton();
    });

    document.getElementById('step-one-next').addEventListener('click', function () {
        goToStep(2);
    });
    document.getElementById('step-two-back').addEventListener('click', function () {
        goToStep(1);
    });
    document.getElementById('step-two-next').addEventListener('click', function () {
        goToStep(3);
    });
    document.getElementById('step-three-back').addEventListener('click', function () {
        goToStep(2);
    });
    deliveryDateInput.addEventListener('input', function () {
        var parsedDate = parseDeliveryDate(deliveryDateInput.value);
        if (parsedDate) {
            var dateParts = parsedDate.split('-');
            calendarMonth = new Date(Number(dateParts[0]), Number(dateParts[1]) - 1, 1);
        }
        updateSaveButton();
        if (!deliveryDateCalendar.hidden) {
            renderDeliveryCalendar();
        }
    });
    deliveryDateToggle.addEventListener('click', function () {
        if (deliveryDateCalendar.hidden) {
            var parsedDate = parseDeliveryDate(deliveryDateInput.value);
            if (parsedDate) {
                var dateParts = parsedDate.split('-');
                calendarMonth = new Date(Number(dateParts[0]), Number(dateParts[1]) - 1, 1);
            }
            renderDeliveryCalendar();
            setDeliveryCalendarOpen(true);
        } else {
            setDeliveryCalendarOpen(false);
        }
    });
    deliveryCalendarPrevious.addEventListener('click', function () {
        calendarMonth.setMonth(calendarMonth.getMonth() - 1);
        renderDeliveryCalendar();
    });
    deliveryCalendarNext.addEventListener('click', function () {
        calendarMonth.setMonth(calendarMonth.getMonth() + 1);
        renderDeliveryCalendar();
    });
    document.addEventListener('click', function (event) {
        if (!deliveryDatePicker.contains(event.target)) {
            setDeliveryCalendarOpen(false);
        }
    });
    deliveryDateCalendar.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            setDeliveryCalendarOpen(false);
            deliveryDateToggle.focus();
        }
    });
    printOrderButton.addEventListener('click', async function () {
        if (!savedOrderId || isPreparingOrderPdf) {
            return;
        }

        isPreparingOrderPdf = true;
        printOrderButton.disabled = true;
        printOrderButton.textContent = 'Preparando PDF...';
        try {
            var pdfBlob = createOrderPdf(savedOrderId);
            var pdfFile = typeof File === 'function'
                ? new File([pdfBlob], 'pedido-' + savedOrderId + '.pdf', {type: 'application/pdf'})
                : null;
            var canSharePdf = false;
            if (pdfFile && navigator.share && navigator.canShare) {
                try {
                    canSharePdf = navigator.canShare({files: [pdfFile]});
                } catch (error) {
                    canSharePdf = false;
                }
            }

            if (canSharePdf) {
                try {
                    await navigator.share({
                        files: [pdfFile],
                        title: 'Pedido #' + savedOrderId,
                        text: 'Pedido de venda #' + savedOrderId + ' - ' + customer.name
                    });
                } catch (error) {
                    if (error.name === 'AbortError') {
                        return;
                    }
                    showOrderPdfLink(
                        'Não foi possível abrir o compartilhamento.',
                        pdfBlob,
                        savedOrderId
                    );
                }
            } else {
                showOrderPdfLink(
                    'PDF gerado. Abra-o no navegador para imprimir ou compartilhar por e-mail ou WhatsApp.',
                    pdfBlob,
                    savedOrderId
                );
            }
        } catch (error) {
            showMessage('Não foi possível preparar o PDF do pedido.', 'error');
        } finally {
            isPreparingOrderPdf = false;
            printOrderButton.disabled = false;
            printOrderButton.textContent = 'Imprimir';
        }
    });
    window.addEventListener('beforeunload', function () {
        if (orderPdfUrl) {
            URL.revokeObjectURL(orderPdfUrl);
        }
    });
    observationInputs.forEach(function (input) {
        input.addEventListener('input', updateSaveButton);
    });

    form.addEventListener('submit', async function (event) {
        event.preventDefault();
        updateSaveButton();
        if (saveButton.disabled) {
            if (currentStep !== 3) {
                showMessage('Conclua as etapas anteriores antes de salvar o pedido.', 'error');
            } else if (!deliveryDateValueInput.value
                || deliveryDateValueInput.value < deliveryDateInput.dataset.minDate) {
                showMessage('Informe uma data de entrega igual ou posterior à data atual.', 'error');
                deliveryDateInput.focus();
            }
            return;
        }

        var total = items.reduce(function (sum, item) {
            return sum + Math.round(item.price * item.quantity * (100 - item.discount)) / 100;
        }, 0);
        if (!await confirmSaveOrder(
            'Confirma salvar o pedido de ' + customer.name + ' no valor de '
            + currency(total) + ' para entrega em '
            + new Intl.DateTimeFormat('pt-BR', {timeZone: 'UTC'}).format(
                new Date(deliveryDateValueInput.value + 'T00:00:00Z')
            ) + ' como pendente (status 99)?'
        )) {
            return;
        }

        isSaving = true;
        saveButton.disabled = true;
        saveButton.textContent = 'Salvando pedido...';
        itemsInput.value = JSON.stringify(items.map(function (item) {
            return {
                id: item.id,
                quantity: item.quantity,
                price: item.price,
                discount: item.discount
            };
        }));
        conditionIdInput.value = conditionSelect.value;
        paymentMethodIdInput.value = paymentMethodSelect.value;

        fetch(form.dataset.saveUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
            },
            body: new URLSearchParams(new FormData(form)).toString()
        })
            .then(function (response) {
                return response.json().then(function (data) {
                    if (!response.ok) {
                        throw new Error(data.message || 'Não foi possível salvar o pedido.');
                    }
                    return data;
                });
            })
            .then(function (data) {
                isSaved = true;
                savedOrderId = data.order_id;
                setCurrentStep(3);
                showMessage(
                    data.duplicate
                        ? 'O pedido #' + data.order_id + ' já havia sido gravado. Nenhuma duplicidade foi criada.'
                        : 'Pedido #' + data.order_id + ' salvo com sucesso como pendente (status 99).'
                            + (data.observation_truncated
                                ? ' A observação 3 foi encurtada para incluir o usuário e o horário.'
                                : ''),
                    'success'
                );
                saveButton.textContent = 'Pedido #' + data.order_id + ' salvo';
                saveButton.parentElement.classList.add('is-saved');
                document.getElementById('step-three-back').hidden = true;
                printOrderButton.hidden = false;
                customerSearch.disabled = true;
                representativeSelect.disabled = true;
                productSearch.disabled = true;
                conditionSelect.disabled = true;
                paymentMethodSelect.disabled = true;
                orderItems.querySelectorAll('button, input').forEach(function (control) {
                    control.disabled = true;
                });
                deliveryDateInput.disabled = true;
                observationInputs.forEach(function (input) {
                    input.disabled = true;
                });
            })
            .catch(function (error) {
                showMessage(error.message, 'error');
                isSaving = false;
                saveButton.textContent = 'Salvar pedido';
                updateSaveButton();
            });
    });

    setCurrentStep(1);
    renderItems();
    loadRepresentatives();
}());
