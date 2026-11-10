<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <?php
        $scriptDirectory = dirname(isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '/index.php');
        $appBasePath = ($scriptDirectory === '.' || $scriptDirectory === DIRECTORY_SEPARATOR)
            ? ''
            : rtrim(str_replace('\\', '/', $scriptDirectory), '/');
        $dashboardUrl = $appBasePath . '/index.php/inovasse-dashboard';
    ?>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Painel Inovasse</title>
    <link href="<?php echo html_escape($appBasePath . '/vendors/fontawesome-free/css/all.min.css'); ?>" rel="stylesheet">
    <link href="<?php echo html_escape($appBasePath . '/css/inovasse-dashboard.css?v=16'); ?>" rel="stylesheet">
</head>
<body>
    <?php
        $selectedTitle = isset($views[$selected_view]) ? $views[$selected_view]['title'] : 'Faturamento';
        $totalColumns = array(
            'QUANTIDADE' => 'QUANTIDADE',
            'VALORTOTAL' => 'VALOR TOTAL',
            'VALORDESCONTO' => 'VALOR DESCONTO'
        );
        $availableTotalColumns = array();
        foreach ($columns as $column) {
            $normalizedColumn = strtoupper(preg_replace('/[^A-Z0-9]/', '', $column));
            if (isset($totalColumns[$normalizedColumn])) {
                $availableTotalColumns[$column] = $normalizedColumn;
            }
        }
        $operationColumn = NULL;
        $amountColumn = NULL;
        foreach ($columns as $column) {
            $normalizedColumn = strtoupper(preg_replace('/[^A-Z0-9]/', '', $column));
            if ($normalizedColumn === 'NOMEOPERACAO') {
                $operationColumn = $column;
            } elseif ($normalizedColumn === 'VALORTOTAL') {
                $amountColumn = $column;
            }
        }
        $firstColumn = count($columns) > 0 ? reset($columns) : NULL;
    ?>
    <div class="dashboard-shell">
        <div class="sidebar-overlay" data-sidebar-close></div>
        <aside class="dashboard-sidebar" id="dashboard-sidebar" aria-label="Menu de consultas">
            <div class="sidebar-heading">
                <span class="brand-mark"><i class="fas fa-chart-line" aria-hidden="true"></i></span>
                <div>
                    <strong>Inovasse</strong>
                    <span>Consultas</span>
                </div>
                <button class="sidebar-close" type="button" aria-label="Fechar menu" data-sidebar-close>
                    <i class="fas fa-times" aria-hidden="true"></i>
                </button>
            </div>

            <form class="query-form" method="get" action="<?php echo html_escape($dashboardUrl); ?>">
                <input type="hidden" name="view" id="selected-view" value="<?php echo html_escape($selected_view); ?>">
                <input type="hidden" name="search" value="1">

                <br>
                <nav class="query-navigation" aria-label="Selecione uma consulta">
                    <?php foreach ($views as $key => $view): ?>
                        <button
                            class="query-option<?php echo $key === $selected_view ? ' is-active' : ''; ?>"
                            type="button"
                            data-view="<?php echo html_escape($key); ?>"
                            aria-pressed="<?php echo $key === $selected_view ? 'true' : 'false'; ?>">
                            <i class="fas <?php
                                echo $key === 'faturamento' ? 'fa-file-invoice-dollar'
                                    : ($key === 'cliente' ? 'fa-users'
                                    : ($key === 'estoque' ? 'fa-boxes' : 'fa-cube'));
                            ?>" aria-hidden="true"></i>
                            <span><?php echo html_escape($view['title']); ?></span>
                        </button>
                    <?php endforeach; ?>
                </nav>

                <div class="sidebar-search">
                    <div class="date-filter" id="date-filter">
                        <label for="data-inicio">Data de início</label>
                        <input id="data-inicio" type="date" name="data_inicio"
                            value="<?php echo html_escape($data_inicio ? $data_inicio : ''); ?>">
                        <label for="data-fim">Data de fim</label>
                        <input id="data-fim" type="date" name="data_fim"
                            value="<?php echo html_escape($data_fim ? $data_fim : ''); ?>">
                    </div>
                    <button class="search-button" type="submit">
                        <i class="fas fa-search" aria-hidden="true"></i>
                        <span>Pesquisar</span>
                    </button>
                </div>
            </form>
        </aside>

        <main class="dashboard-main">
            <header class="topbar">
                <button class="hamburger-button" type="button" aria-label="Abrir menu de consultas"
                    aria-controls="dashboard-sidebar" aria-expanded="false" data-sidebar-toggle>
                    <span></span><span></span><span></span>
                </button>
                <h1 class="report-title">
                    <img
                        class="report-logo"
                        src="<?php echo html_escape($appBasePath . '/IMG/Logo1.png'); ?>"
                        alt="<?php echo html_escape($selectedTitle); ?>">
                </h1>
                <?php if ($searched && $error === NULL && count($rows) > 0): ?>
                    <button
                        class="export-button"
                        type="button"
                        data-export-table
                        data-export-title="<?php echo html_escape($selectedTitle); ?>"
                        aria-label="Exportar resultados para Excel">
                        <i class="fas fa-file-excel" aria-hidden="true"></i>
                        <span>Exportar Excel</span>
                    </button>
                <?php endif; ?>
                <label class="theme-switch" for="theme-toggle">
                    <i class="fas fa-moon theme-switch-icon" aria-hidden="true"></i>
                    <span class="theme-switch-label">Modo escuro</span>
                    <input id="theme-toggle" type="checkbox" role="switch" aria-label="Habilitar modo escuro" checked>
                    <span class="theme-switch-track" aria-hidden="true"><span class="theme-switch-thumb"></span></span>
                </label>
                <span class="topbar-tag">INOVASSE-PETFIVE</span>
            </header>

            <section class="page-content">
                <?php if ($error !== NULL): ?>
                    <div class="notice notice-error" role="alert">
                        <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
                        <span><?php echo html_escape($error); ?></span>
                    </div>
                <?php elseif (!$searched): ?>
                    <div class="empty-state">
                        <div class="empty-icon"><i class="fas fa-table" aria-hidden="true"></i></div>
                        <h2>Escolha uma consulta</h2>
                        <p>Abra o menu, selecione uma opção e pressione Pesquisar.</p>
                    </div>
                <?php elseif (count($rows) === 0): ?>
                    <div class="empty-state">
                        <div class="empty-icon"><i class="fas fa-search" aria-hidden="true"></i></div>
                        <h2>Nenhum registro encontrado</h2>
                        <p>Não há dados para os critérios informados.</p>
                    </div>
                <?php else: ?>
                    <?php if ($selected_view === 'faturamento' && $operationColumn !== NULL && $amountColumn !== NULL): ?>
                        <section class="operation-summary" aria-label="Totais por tipo de operação">
                            <div class="operation-summary-heading">
                                <div>
                                    <h2>Totais por operação e itens nas notas</h2>
                                </div>
                                <span>--</span>
                            </div>
                            <div class="operation-summary-grid">
                                <?php foreach (array('ENTRADA', 'VENDA', 'BONIFICACAO', 'DEVOLUCAO', 'REMESSA') as $operationName): ?>
                                    <?php $operationAmount = 'R$ ' . number_format(isset($operation_totals[$operationName]) ? $operation_totals[$operationName] : 0, 2, ',', '.'); ?>
                                    <div
                                        class="operation-summary-item"
                                        data-operation-name="<?php echo html_escape($operationName); ?>"
                                        data-operation-quantity="<?php
                                            $operationQuantity = isset($operation_quantities[$operationName]) ? $operation_quantities[$operationName] : 0;
                                            $formattedOperationQuantity = number_format($operationQuantity, 3, ',', '.');
                                            echo html_escape(rtrim(rtrim($formattedOperationQuantity, '0'), ','));
                                        ?>"
                                        data-operation-value="<?php echo html_escape($operationAmount); ?>">
                                        <div class="operation-summary-title">
                                            <span class="operation-name"><?php echo html_escape($operationName); ?></span>
                                            <span class="operation-count"><?php echo html_escape(rtrim(rtrim($formattedOperationQuantity, '0'), ',')); ?></span>
                                        </div>
                                        <strong class="operation-amount"><?php echo $operationAmount; ?></strong>
                                    </div>
                                <?php endforeach; ?>
                                <?php if ($canceled_summary !== NULL): ?>
                                    <?php
                                        $canceledQuantity = number_format($canceled_summary['quantity'], 3, ',', '.');
                                        $canceledQuantity = rtrim(rtrim($canceledQuantity, '0'), ',');
                                        $canceledAmount = 'R$ ' . number_format($canceled_summary['amount'], 2, ',', '.');
                                    ?>
                                    <div
                                        class="operation-summary-item canceled-summary-item"
                                        data-operation-name="Cancelada"
                                        data-operation-quantity="<?php echo html_escape($canceledQuantity); ?>"
                                        data-operation-value="<?php echo html_escape($canceledAmount); ?>">
                                        <div class="operation-summary-title">
                                            <span class="operation-name">Cancelada</span>
                                            <span class="operation-count"><?php echo html_escape($canceledQuantity); ?></span>
                                        </div>
                                        <strong class="operation-amount"><?php echo $canceledAmount; ?></strong>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </section>
                    <?php endif; ?>
                    <div class="table-card">
                        <div class="table-toolbar">
                            <div>
                                <strong>Resultado da pesquisa</strong>
                                <span>Exibindo até 500 registros</span>
                            </div>
                            <span class="table-badge"><?php echo html_escape($selectedTitle); ?></span>
                        </div>
                        <div class="table-scroll">
                            <table id="query-results">
                                <thead>
                                    <tr>
                                        <?php foreach ($columns as $column): ?>
                                            <?php $normalizedColumn = strtoupper(preg_replace('/[^A-Z0-9]/', '', $column)); ?>
                                            <?php $isRightAligned = $normalizedColumn === 'QUANTIDADE' || ($selected_view === 'estoque' && $normalizedColumn === 'NUMESTOQUE'); ?>
                                            <th scope="col" class="<?php echo $isRightAligned ? 'quantity-value' : ''; ?>"><?php echo html_escape($column); ?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($rows as $row): ?>
                                        <tr>
                                            <?php foreach ($columns as $column): ?>
                                                <?php
                                                    $value = array_key_exists($column, $row) ? $row[$column] : NULL;
                                                    $normalizedColumn = strtoupper(preg_replace('/[^A-Z0-9]/', '', $column));
                                                    $isCurrency = in_array($normalizedColumn, array('VALORUNITARIO', 'VALORTOTAL', 'VALORDESCONTO'), TRUE);
                                                    $isQuantity = $normalizedColumn === 'QUANTIDADE';
                                                    $isStockNumber = $selected_view === 'estoque' && $normalizedColumn === 'NUMESTOQUE';
                                                    $isRightAligned = $isQuantity || $isStockNumber;
                                                ?>
                                                <td class="<?php echo $isRightAligned ? 'quantity-value' : ''; ?>"><?php
                                                    if ($value === NULL) {
                                                        echo '<span class="null-value">—</span>';
                                                    } elseif ($isCurrency && is_numeric($value)) {
                                                        echo 'R$ ' . number_format((float) $value, 2, ',', '.');
                                                    } elseif ($isRightAligned && is_numeric($value)) {
                                                        $formattedQuantity = number_format((float) $value, 3, ',', '.');
                                                        echo rtrim(rtrim($formattedQuantity, '0'), ',');
                                                    } elseif ($normalizedColumn === 'DATA'
                                                        && is_string($value)
                                                        && preg_match('/^(\d{4})-(\d{2})-(\d{2})(?:\s|T|$)/', $value, $dateParts)
                                                        && checkdate((int) $dateParts[2], (int) $dateParts[3], (int) $dateParts[1])) {
                                                        echo $dateParts[3] . '-' . $dateParts[2] . '-' . $dateParts[1];
                                                    } else {
                                                        echo html_escape((string) $value);
                                                    }
                                                ?></td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <?php if ($selected_view === 'faturamento' && count($availableTotalColumns) > 0): ?>
                                    <tfoot>
                                        <tr>
                                            <?php foreach ($columns as $column): ?>
                                                <?php if (isset($availableTotalColumns[$column])): ?>
                                                    <?php
                                                        $totalKey = $availableTotalColumns[$column];
                                                        $isQuantity = $totalKey === 'QUANTIDADE';
                                                        $decimals = $isQuantity ? 3 : 2;
                                                    ?>
                                                    <td class="total-value<?php echo $isQuantity ? ' quantity-value' : ''; ?>"><?php
                                                        if ($totalKey === 'VALORTOTAL' || $totalKey === 'VALORDESCONTO') {
                                                            echo 'R$ ';
                                                        }
                                                        $formattedTotal = number_format(isset($totals[$totalKey]) ? $totals[$totalKey] : 0, $decimals, ',', '.');
                                                        echo $isQuantity ? rtrim(rtrim($formattedTotal, '0'), ',') : $formattedTotal;
                                                    ?></td>
                                                <?php elseif ($column === $firstColumn): ?>
                                                    <td class="total-label">Total exibido</td>
                                                <?php else: ?>
                                                    <td></td>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </tr>
                                    </tfoot>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>
            </section>
            <footer class="dashboard-footer">
                <span>&copy; <?php echo date('Y'); ?>. Desenvolvido por
                    <a href="https://www.idealbrasil.net" target="_blank" rel="noopener noreferrer">Ideal Brasil Sistemas</a>
                </span>
            </footer>
        </main>
    </div>
    <script src="<?php echo html_escape($appBasePath . '/js/inovasse-dashboard.js?v=8'); ?>"></script>
</body>
</html>
