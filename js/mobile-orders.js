(function () {
    'use strict';

    var app = document.getElementById('order-app');
    var form = document.getElementById('order-form');
    if (!app || !form) {
        return;
    }

    var customerSearch = document.getElementById('customer-search');
    var customerResults = document.getElementById('customer-results');
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
    var countSummary = document.getElementById('order-summary-count');
    var totalSummary = document.getElementById('order-summary-total');
    var items = [];
    var customer = null;
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

    function makeSearchResult(label, detail, onClick, buttonLabel) {
        var row = document.createElement('div');
        row.className = 'order-search-result';

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
        productSearch.disabled = true;
        productSearch.value = '';
        productResults.hidden = true;
        productResults.textContent = '';
        conditionSelect.disabled = true;
        paymentMethodSelect.disabled = true;
        conditionSelect.innerHTML = '<option value="">Selecione um cliente</option>';
        paymentMethodSelect.innerHTML = '<option value="">Selecione um cliente</option>';
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

        productSearch.disabled = false;
        productSearch.placeholder = 'Descrição ou código de barras';
        conditionSelect.disabled = true;
        paymentMethodSelect.disabled = true;
        conditionSelect.innerHTML = '<option value="">Carregando condições...</option>';
        paymentMethodSelect.innerHTML = '<option value="">Carregando formas...</option>';
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
        fetch(app.dataset.customersUrl + '?q=' + encodeURIComponent(term), {
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
                    'Adicionar'
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
        var existing = items.find(function (item) {
            return item.id === product.id;
        });
        if (existing) {
            existing.quantity += 1;
        } else {
            items.push({
                id: product.id,
                description: product.description,
                price: Number(product.price),
                quantity: 1
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
                lineTotal.textContent = currency(
                    Math.round(item.price * item.quantity * 100) / 100
                );
                details.appendChild(title);
                details.appendChild(unitPrice);
                details.appendChild(lineTotal);

                var quantity = document.createElement('div');
                quantity.className = 'quantity-control';
                var quantityLabel = document.createElement('label');
                quantityLabel.setAttribute('for', 'quantity-' + item.id);
                quantityLabel.textContent = 'Qtd.';
                var quantityInput = document.createElement('input');
                quantityInput.type = 'number';
                quantityInput.id = 'quantity-' + item.id;
                quantityInput.min = '0.001';
                quantityInput.step = '0.001';
                quantityInput.value = String(item.quantity);
                quantityInput.setAttribute('aria-label', 'Quantidade de ' + item.description);
                quantityInput.addEventListener('change', function () {
                    var newQuantity = Number(quantityInput.value);
                    if (!Number.isFinite(newQuantity) || newQuantity <= 0 || newQuantity > 100000) {
                        quantityInput.value = String(item.quantity);
                        return;
                    }
                    items[index].quantity = newQuantity;
                    renderItems();
                });

                var remove = document.createElement('button');
                remove.type = 'button';
                remove.className = 'remove-order-item';
                remove.textContent = '−';
                remove.setAttribute('aria-label', 'Remover item do pedido');
                remove.addEventListener('click', function () {
                    items.splice(index, 1);
                    renderItems();
                });

                quantity.appendChild(quantityLabel);
                quantity.appendChild(quantityInput);
                quantity.appendChild(remove);
                article.appendChild(symbol);
                article.appendChild(details);
                article.appendChild(quantity);
                orderItems.appendChild(article);
            });
        }

        var totalQuantity = items.reduce(function (total, item) {
            return total + item.quantity;
        }, 0);
        var total = items.reduce(function (sum, item) {
            return sum + Math.round(item.price * item.quantity * 100) / 100;
        }, 0);
        countSummary.textContent = new Intl.NumberFormat('pt-BR', {
            maximumFractionDigits: 3
        }).format(totalQuantity) + ' itens · ' + items.length + ' produtos';
        totalSummary.textContent = currency(total);
        itemsInput.value = JSON.stringify(items.map(function (item) {
            return {id: item.id, quantity: item.quantity, price: item.price};
        }));
        updateSaveButton();
    }

    function setOptions(select, options, defaultId, emptyLabel, labelBuilder) {
        select.textContent = '';
        var placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = emptyLabel;
        select.appendChild(placeholder);

        options.forEach(function (option) {
            var element = document.createElement('option');
            element.value = String(option.id);
            element.textContent = labelBuilder(option);
            if (Number(option.id) === Number(defaultId)) {
                element.selected = true;
            }
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
                    data.default_condition_id,
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
                    data.default_payment_method_id,
                    'Selecione a forma',
                    function (method) { return method.description;                     }
                );
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
            || !conditionSelect.value || !paymentMethodSelect.value;
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

    productSearch.addEventListener('input', function () {
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

    conditionSelect.addEventListener('change', updateSaveButton);
    paymentMethodSelect.addEventListener('change', updateSaveButton);

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        updateSaveButton();
        if (saveButton.disabled) {
            return;
        }

        var total = items.reduce(function (sum, item) {
            return sum + Math.round(item.price * item.quantity * 100) / 100;
        }, 0);
        if (!window.confirm(
            'Confirma salvar o pedido de ' + customer.name + ' no valor de '
            + currency(total) + ' como pendente (status 99)?'
        )) {
            return;
        }

        isSaving = true;
        saveButton.disabled = true;
        saveButton.textContent = 'Salvando pedido...';
        itemsInput.value = JSON.stringify(items.map(function (item) {
            return {id: item.id, quantity: item.quantity, price: item.price};
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
                document.getElementById('order-step-two').classList.add('is-current');
                document.getElementById('order-step-one').classList.remove('is-current');
                document.getElementById('order-step-one').removeAttribute('aria-current');
                document.getElementById('order-step-two').setAttribute('aria-current', 'step');
                showMessage(
                    data.duplicate
                        ? 'O pedido #' + data.order_id + ' já havia sido gravado. Nenhuma duplicidade foi criada.'
                        : 'Pedido #' + data.order_id + ' salvo com sucesso como pendente (status 99).',
                    'success'
                );
                saveButton.textContent = 'Pedido #' + data.order_id + ' salvo';
                customerSearch.disabled = true;
                productSearch.disabled = true;
                conditionSelect.disabled = true;
                paymentMethodSelect.disabled = true;
                orderItems.querySelectorAll('button, input').forEach(function (control) {
                    control.disabled = true;
                });
            })
            .catch(function (error) {
                showMessage(error.message, 'error');
                isSaving = false;
                saveButton.textContent = 'Salvar pedido';
                updateSaveButton();
            });
    });

    renderItems();
}());
