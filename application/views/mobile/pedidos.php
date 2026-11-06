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
    <link href="<?php echo html_escape($appBasePath . '/css/mobile-orders-mockup.css?v=23'); ?>" rel="stylesheet">
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
            Voltar
        </a>
    </header>

    <main class="orders-main"
        id="order-app"
        data-discount-enabled="<?php echo html_escape($discount_enabled ? '1' : '0'); ?>"
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
                    <strong>Dados</strong>
                </div>
                <span class="step-connector" aria-hidden="true"></span>
                <div class="order-step" id="order-step-two">
                    <span>2</span>
                    <strong>Produtos</strong>
                </div>
                <span class="step-connector" aria-hidden="true"></span>
                <div class="order-step" id="order-step-three">
                    <span>3</span>
                    <strong>Entrega</strong>
                </div>
            </nav>

            <div id="step-panel-one" class="order-step-panel">
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

                <div class="orders-actions">
                    <span></span>
                    <button class="primary-action" id="step-one-next" type="button">
                        Próximo <span aria-hidden="true">&rarr;</span>
                    </button>
                </div>
            </div>

            <div id="step-panel-two" class="order-step-panel" hidden>
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

            <section class="order-summary" aria-labelledby="step-two-summary-heading">
                <div>
                    <h3 id="step-two-summary-heading">Resumo do pedido</h3>
                    <p data-order-summary-count>0 itens · 0 produtos</p>
                </div>
                <strong data-order-summary-total>R$ 0,00</strong>
            </section>

                <div class="orders-actions">
                    <button class="secondary-action" id="step-two-back" type="button">
                        <span aria-hidden="true">&larr;</span> Voltar
                    </button>
                    <button class="primary-action" id="step-two-next" type="button">
                        Próximo <span aria-hidden="true">&rarr;</span>
                    </button>
                </div>
            </div>

            <div id="step-panel-three" class="order-step-panel" hidden>
                <section class="order-card" aria-labelledby="delivery-heading">
                    <div class="order-section-heading">
                        <span class="section-number">05</span>
                        <div>
                            <h3 id="delivery-heading">Entrega e observações</h3>
                            <p>Informe a data de entrega e observações para o pedido.</p>
                        </div>
                    </div>
                    <label class="mockup-label" for="delivery-date">Data de entrega</label>
                    <input type="hidden" id="delivery-date-value" name="delivery_date"
                        value="<?php echo html_escape(date('Y-m-d')); ?>">
                    <div class="delivery-date-picker" id="delivery-date-picker">
                        <div class="delivery-date-input-row">
                            <input class="order-text-input" id="delivery-date" type="text"
                                inputmode="numeric" placeholder="dd/mm/aaaa" pattern="[0-9]{2}/[0-9]{2}/[0-9]{4}"
                                maxlength="10" data-min-date="<?php echo html_escape(date('Y-m-d')); ?>"
                                value="<?php echo html_escape(date('d/m/Y')); ?>"
                                aria-haspopup="dialog" aria-expanded="false"
                                aria-controls="delivery-date-calendar" required>
                            <button class="delivery-date-toggle" id="delivery-date-toggle" type="button"
                                aria-label="Abrir calendário" aria-expanded="false"
                                aria-controls="delivery-date-calendar" title="Abrir calendário">
                                <svg aria-hidden="true" viewBox="0 0 24 24" focusable="false">
                                    <rect x="3" y="5" width="18" height="16" rx="2"></rect>
                                    <path d="M16 3v4M8 3v4M3 10h18"></path>
                                </svg>
                            </button>
                        </div>
                        <div class="delivery-date-calendar" id="delivery-date-calendar"
                            role="dialog" aria-label="Escolher data de entrega" hidden>
                            <div class="delivery-calendar-heading">
                                <button type="button" id="delivery-calendar-previous"
                                    aria-label="Mês anterior">&lsaquo;</button>
                                <strong id="delivery-calendar-month"></strong>
                                <button type="button" id="delivery-calendar-next"
                                    aria-label="Próximo mês">&rsaquo;</button>
                            </div>
                            <div class="delivery-calendar-grid" id="delivery-calendar-grid"
                                role="group" aria-label="Dias do mês"></div>
                        </div>
                    </div>

                    <div class="order-observations">
                        <div>
                            <label class="mockup-label" for="observ-1">Observação 1</label>
                            <input class="order-text-input" id="observ-1" name="observ_1"
                                type="text" maxlength="80">
                        </div>
                        <div>
                            <label class="mockup-label" for="observ-2">Observação 2</label>
                            <input class="order-text-input" id="observ-2" name="observ_2"
                                type="text" maxlength="80">
                        </div>
                        <div>
                            <label class="mockup-label" for="observ-3">Observação 3</label>
                            <input class="order-text-input" id="observ-3" name="observ_3"
                                type="text" maxlength="80">
                        </div>
                    </div>
                </section>

            <section class="order-summary" aria-labelledby="summary-heading">
                <div>
                    <h3 id="summary-heading">Resumo do pedido</h3>
                    <p data-order-summary-count>0 itens · 0 produtos</p>
                </div>
                <strong data-order-summary-total>R$ 0,00</strong>
            </section>

            <div class="orders-actions">
                <button class="secondary-action" id="step-three-back" type="button">
                    <span aria-hidden="true">&larr;</span> Voltar
                </button>
                <button class="primary-action" id="save-order" type="submit" disabled>
                    Salvar pedido <span aria-hidden="true">&rarr;</span>
                </button>
                <button class="secondary-action" id="print-order" type="button" hidden>Imprimir</button>
            </div>
            </div>
        </form>
    </main>

    <dialog class="order-confirmation" id="order-confirmation"
        aria-labelledby="order-confirmation-title" aria-describedby="order-confirmation-message">
        <div class="order-confirmation-content">
            <span class="order-confirmation-icon" aria-hidden="true">?</span>
            <h2 id="order-confirmation-title">Confirme o pedido?</h2>
            <p class="order-confirmation-attention">Atenção</p>
            <p class="order-confirmation-message" id="order-confirmation-message"></p>
            <form method="dialog" class="order-confirmation-actions">
                <button class="order-confirmation-save" type="submit" value="save">Salvar</button>
                <button class="order-confirmation-cancel" type="submit" value="cancel">Cancelar</button>
            </form>
        </div>
    </dialog>

    <footer class="dashboard-footer">ForçaV · O pedido será gravado como pendente</footer>
    <script src="<?php echo html_escape($appBasePath . '/js/mobile-orders.js?v=19'); ?>" defer></script>
</body>
</html>
