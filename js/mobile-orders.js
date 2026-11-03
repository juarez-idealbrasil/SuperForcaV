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
    var message = document.getElementById('order-message');
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

    function showMessage(text, type) {
        message.textContent = text;
        message.className = 'order-message is-' + type;
        message.hidden = false;
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

    function makeSearchResult(label, detail, onClick, buttonLabel, imageData) {
        var row = document.createElement('div');
        row.className = 'order-search-result';

        if (imageData) {
            var image = document.createElement('img');
            image.className = 'order-result-image';
            image.src = imageData.url;
            image.alt = '';
            image.loading = 'lazy';
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

        var button = document.createElement('button');
        button.type = 'button';
        button.className = 'order-result-button';
        button.textContent = buttonLabel;
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
                    'Adicionar',
                    {
                        url: product.image_url,
                        fallbackUrl: product.fallback_image_url
                    }
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
        } else {
            items.push({
                id: product.id,
                description: product.description,
                price: Number(product.price),
                quantity: 1,
                discount: 0
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

                var symbol = document.createElement('span');
                symbol.className = 'product-symbol';
                symbol.setAttribute('aria-hidden', 'true');
                symbol.textContent = 'P';

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
        saveButton.disabled = isSaving || isSaved || !customer || items.length === 0
            || !conditionSelect.value || !paymentMethodSelect.value
            || currentStep !== 3 || !deliveryDateInput.value
            || deliveryDateInput.value < deliveryDateInput.min
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
    deliveryDateInput.addEventListener('change', updateSaveButton);
    observationInputs.forEach(function (input) {
        input.addEventListener('input', updateSaveButton);
    });

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        updateSaveButton();
        if (saveButton.disabled) {
            if (currentStep !== 3) {
                showMessage('Conclua as etapas anteriores antes de salvar o pedido.', 'error');
            } else if (!deliveryDateInput.value || deliveryDateInput.value < deliveryDateInput.min) {
                showMessage('Informe uma data de entrega igual ou posterior à data atual.', 'error');
                deliveryDateInput.focus();
            }
            return;
        }

        var total = items.reduce(function (sum, item) {
            return sum + Math.round(item.price * item.quantity * (100 - item.discount)) / 100;
        }, 0);
        if (!window.confirm(
            'Confirma salvar o pedido de ' + customer.name + ' no valor de '
            + currency(total) + ' para entrega em '
            + new Intl.DateTimeFormat('pt-BR', {timeZone: 'UTC'}).format(
                new Date(deliveryDateInput.value + 'T00:00:00Z')
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
