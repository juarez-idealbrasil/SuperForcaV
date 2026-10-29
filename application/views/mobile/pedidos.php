<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$appBasePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#0b3c5d">
    <title>ForçaV - Novo pedido</title>
    <link href="<?php echo html_escape($appBasePath . '/css/mobile-dashboard.css?v=orders-live-1'); ?>" rel="stylesheet">
    <link href="<?php echo html_escape($appBasePath . '/css/mobile-orders-mockup.css?v=4'); ?>" rel="stylesheet">
</head>
<body class="mobile-dashboard orders-mockup orders-live">
    <header class="dashboard-header">
        <div class="header-brand">
            <span class="brand-mark" aria-hidden="true">SFV</span>
            <div class="header-copy">
                <h1>Novo pedido</h1>
                <p>Olá, <?php echo html_escape($user_name); ?></p>
            </div>
        </div>
        <a class="orders-back-link" href="<?php echo html_escape($appBasePath . '/index.php/mobile/dashboard'); ?>">
            Voltar ao painel
        </a>
    </header>

    <main class="orders-main"
        id="order-app"
        data-representatives-url="<?php echo html_escape($appBasePath . '/index.php/mobile/dashboard/pedidos_representantes'); ?>"
        data-customers-url="<?php echo html_escape($appBasePath . '/index.php/mobile/dashboard/pedidos_clientes'); ?>"
        data-products-url="<?php echo html_escape($appBasePath . '/index.php/mobile/dashboard/pedidos_produtos'); ?>"
        data-payments-url="<?php echo html_escape($appBasePath . '/index.php/mobile/dashboard/pedidos_pagamentos'); ?>">
        <div class="orders-title-row">
            <div>
                <p class="orders-kicker">PEDIDO DE VENDA</p>
            </div>
            <span class="live-badge">FORÇA V</span>
        </div>

        <div id="order-message" class="order-message" role="status" aria-live="polite" hidden></div>

        <form id="order-form" method="post"
            action="<?php echo html_escape($appBasePath . '/index.php/mobile/dashboard/pedidos_salvar'); ?>"
            data-save-url="<?php echo html_escape($appBasePath . '/index.php/mobile/dashboard/pedidos_salvar'); ?>">
            <input type="hidden" id="order-token" name="order_token" value="<?php echo html_escape($order_token); ?>">
            <input type="hidden" id="customer-id" name="customer_id" value="">
            <input type="hidden" id="order-items-json" name="items" value="[]">
            <input type="hidden" id="condition-id" name="condition_id" value="">
            <input type="hidden" id="payment-method-id" name="payment_method_id" value="">

            <nav class="order-steps" aria-label="Etapas do pedido">
                <div class="order-step is-current" id="order-step-one" aria-current="step">
                    <span>1</span>
                    <strong>Pedido</strong>
                </div>
                <span class="step-connector" aria-hidden="true"></span>
                <div class="order-step" id="order-step-two">
                    <span>2</span>
                    <strong>Revisão e gravação</strong>
                </div>
            </nav>

            <section class="order-card" aria-labelledby="representative-heading">
                <div class="order-section-heading">
                    <span class="section-number">01</span>
                    <div>
                        <h3 id="representative-heading">Representante</h3>
                        <p>Opcional. Selecione para limitar a busca aos clientes vinculados.</p>
                    </div>
                </div>
                <label class="mockup-label" for="representative-select">Selecionar representante</label>
                <select id="representative-select" disabled>
                    <option value="">Carregando representantes...</option>
                </select>
            </section>

            <section class="order-card" aria-labelledby="customer-heading">
                <div class="order-section-heading">
                    <span class="section-number">02</span>
                    <div>
                        <h3 id="customer-heading">Cliente</h3>
                        <p>O representante padrão será identificado pelo perfil do cliente.</p>
                    </div>
                </div>
                <label class="mockup-label" for="customer-search">Buscar cliente</label>
                <div class="mockup-search">
                    <span aria-hidden="true">&#9906;</span>
                    <input id="customer-search" type="search" autocomplete="off"
                        placeholder="Nome, fantasia ou documento" aria-controls="customer-results">
                </div>
                <div id="customer-results" class="order-search-results" role="list" aria-label="Resultados de clientes" hidden></div>
                <div id="selected-customer" class="selected-customer" hidden></div>
            </section>

            <section class="order-card" aria-labelledby="payment-heading">
                <div class="order-section-heading">
                    <span class="section-number">03</span>
                    <div>
                        <h3 id="payment-heading">Pagamento</h3>
                        <p>As opções respeitam as regras de venda e os limites cadastrados para o cliente.</p>
                    </div>
                </div>
                <div class="order-payment-grid">
                    <div>
                        <label class="mockup-label" for="condition-select">Condição de pagamento</label>
                        <select id="condition-select" disabled required>
                            <option value="">Selecione um cliente</option>
                        </select>
                    </div>
                    <div>
                        <label class="mockup-label" for="payment-method-select">Forma de pagamento</label>
                        <select id="payment-method-select" disabled required>
                            <option value="">Selecione um cliente</option>
                        </select>
                    </div>
                </div>
            </section>

            <section class="order-card" aria-labelledby="products-heading">
                <div class="order-section-heading">
                    <span class="section-number">04</span>
                    <div>
                        <h3 id="products-heading">Produtos</h3>
                        <p>Os valores exibidos seguem o catálogo do cliente.</p>
                    </div>
                </div>
                <label class="mockup-label" for="product-search">Buscar produto</label>
                <div class="mockup-search">
                    <span aria-hidden="true">&#9906;</span>
                    <input id="product-search" type="search" autocomplete="off"
                        placeholder="Descrição ou código de barras" aria-controls="product-results" disabled>
                </div>
                <div id="product-results" class="order-search-results" role="list" aria-label="Resultados de produtos" hidden></div>
                <div id="order-items" class="mock-product-list">
                    <p class="empty-order">Selecione um cliente para buscar produtos.</p>
                </div>
            </section>

            <section class="order-summary" aria-labelledby="summary-heading">
                <div>
                    <h3 id="summary-heading">Resumo do pedido</h3>
                    <p id="order-summary-count">0 itens · 0 produtos</p>
                </div>
                <strong id="order-summary-total">R$ 0,00</strong>
            </section>

            <div class="orders-actions">
                <a class="secondary-action" href="<?php echo html_escape($appBasePath . '/index.php/mobile/dashboard'); ?>">
                    Cancelar
                </a>
                <button class="primary-action" id="save-order" type="submit" disabled>
                    Salvar pedido <span aria-hidden="true">&rarr;</span>
                </button>
            </div>
        </form>
    </main>

    <footer class="dashboard-footer">ForçaV · O pedido será gravado como pendente</footer>
    <script src="<?php echo html_escape($appBasePath . '/js/mobile-orders.js?v=3'); ?>" defer></script>
</body>
</html>
