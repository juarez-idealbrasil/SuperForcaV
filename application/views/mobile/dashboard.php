<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include_once APPPATH . 'helpers/function_helper.php';

$appBasePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$indicators = $dashboard !== NULL ? $dashboard['indicators'] : array();
$dailySales = $dashboard !== NULL ? $dashboard['daily_sales'] : array();
$rankings = $dashboard !== NULL ? $dashboard['rankings'] : array();
$customers = $dashboard !== NULL ? $dashboard['customers'] : array();
$revenue = isset($indicators['FATURAMENTO_TOTAL']) ? (float) $indicators['FATURAMENTO_TOTAL'] : 0;
$salesCount = isset($indicators['QTD_VENDAS']) ? (int) $indicators['QTD_VENDAS'] : 0;
$averageTicket = isset($indicators['TICKET_MEDIO']) ? (float) $indicators['TICKET_MEDIO'] : 0;
$activeCustomers = isset($indicators['CLIENTES_ATIVOS']) ? (int) $indicators['CLIENTES_ATIVOS'] : 0;
$revenuePrevious = isset($indicators['FATURAMENTO_VARIACAO']) ? (float) $indicators['FATURAMENTO_VARIACAO'] : 0;
$salesPrevious = isset($indicators['QTD_VENDAS_VARIACAO']) ? (int) $indicators['QTD_VENDAS_VARIACAO'] : 0;
$ticketPrevious = isset($indicators['TICKET_MEDIO_VARIACAO']) ? (float) $indicators['TICKET_MEDIO_VARIACAO'] : 0;
$customersPrevious = isset($indicators['CLIENTES_ATIVOS_VARIACAO']) ? (int) $indicators['CLIENTES_ATIVOS_VARIACAO'] : 0;
$revenueChange = $revenuePrevious != 0 ? (($revenue - $revenuePrevious) / abs($revenuePrevious)) * 100 : 0;
$salesChange = $salesPrevious != 0 ? (($salesCount - $salesPrevious) / abs($salesPrevious)) * 100 : 0;
$ticketChange = $ticketPrevious != 0 ? (($averageTicket - $ticketPrevious) / abs($ticketPrevious)) * 100 : 0;
$customersChange = $customersPrevious != 0 ? (($activeCustomers - $customersPrevious) / abs($customersPrevious)) * 100 : 0;
$maxDailyRevenue = 0;
foreach ($dailySales as $dailySale) {
    $maxDailyRevenue = max($maxDailyRevenue, (float) $dailySale['VALOR_TOTAL']);
}
$customerRevenue = 0;
foreach ($customers as $customer) {
    $customerRevenue += (float) $customer['VALOR_TOTAL'];
}
$rankings = is_array($rankings) ? $rankings : array();

$formatChange = function ($change) {
    $prefix = $change > 0 ? '+' : '';
    $class = $change > 0 ? 'positive' : ($change < 0 ? 'negative' : 'neutral');
    return array('class' => $class, 'text' => $prefix . number_format($change, 1, ',', '.') . '% vs. ano anterior');
};
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#0b3c5d">
    <title>ForçaV - Visão Geral</title>
    <link href="<?php echo html_escape($appBasePath . '/css/mobile-dashboard.css?v=filters-menu-20'); ?>" rel="stylesheet">
</head>
<body class="mobile-dashboard dashboard-page">
    <header class="dashboard-header">
        <div class="header-brand">
            <span class="brand-mark" aria-hidden="true">SFV</span>
            <div class="header-copy">
                <h1>Visão Geral do Negócio</h1>
                <p>Acompanhe os principais indicadores · Atualizado <?php echo date('d/m/Y H:i'); ?></p>
            </div>
        </div>
        <div class="header-user">
            <span><?php echo html_escape($user_name); ?></span>
            <details class="filter-menu" id="dashboard-filter-menu">
                <summary class="filter-menu-toggle">
                    <span class="hamburger-icon" aria-hidden="true"><span></span></span>
                    <span>Menu</span>
                </summary>
                <button type="button" class="filter-backdrop" aria-label="Fechar menu"></button>
                <div class="filters">
                    <div class="filter-panel-heading">
                        <h2>Menu do painel</h2>
                        <form class="menu-logout-form" method="post"
                            action="<?php echo html_escape($appBasePath . '/index.php/mobile/auth/logout'); ?>">
                            <button type="submit" class="menu-logout-button menu-logout-button-top">
                                <span class="menu-logout-icon" aria-hidden="true">&times;</span>
                                <span>Sair</span>
                            </button>
                        </form>
                        <button type="button" class="filter-close" aria-label="Fechar menu">
                            <span aria-hidden="true">&times;</span>
                            <span>Fechar</span>
                        </button>
                    </div>
                    <a class="orders-menu-link" href="<?php echo html_escape($appBasePath . '/index.php/mobile/dashboard/pedidos'); ?>">
                        <span class="orders-menu-icon" aria-hidden="true">&#43;</span>
                        <span>Pedidos</span>
                        <span class="orders-menu-arrow" aria-hidden="true">&rsaquo;</span>
                    </a>
                    <details class="filter-options" id="dashboard-filter-options">
                        <summary class="orders-menu-link filters-menu-link">
                            <span class="orders-menu-icon" aria-hidden="true">&#43;</span>
                            <span>Filtros</span>
                            <span class="orders-menu-arrow" aria-hidden="true">&rsaquo;</span>
                        </summary>
                        <form class="filter-options-form" method="get"
                            action="<?php echo html_escape($appBasePath . '/index.php/mobile/dashboard'); ?>">
                            <div class="filter">
                                <label for="selectEmpresa">Empresa</label>
                                <select id="selectEmpresa" name="empresa" required>
                                    <?php foreach ($companies as $company): ?>
                                        <option value="<?php echo (int) $company['ID_EMP']; ?>"
                                            <?php echo (int) $company['ID_EMP'] === $company_id ? 'selected' : ''; ?>>
                                            <?php echo html_escape($company['RAZ_SOC']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="filter-select-range">
                                <div class="filter">
                                    <label for="selectEquipe">Equipe</label>
                                    <select id="selectEquipe" name="equipe">
                                        <option value="">Todas as equipes</option>
                                        <?php foreach ($filter_options['teams'] as $team): ?>
                                            <option value="<?php echo (int) $team['ID_FILTRO']; ?>"
                                                <?php echo (int) $team['ID_FILTRO'] === $team_id ? 'selected' : ''; ?>>
                                                <?php echo html_escape($team['NOME']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="filter">
                                    <label for="selectRepresentante">Representante</label>
                                    <select id="selectRepresentante" name="representante">
                                        <option value="">Todos os representantes</option>
                                        <?php foreach ($filter_options['representatives'] as $representative): ?>
                                            <option value="<?php echo (int) $representative['ID_FILTRO']; ?>"
                                                <?php echo (int) $representative['ID_FILTRO'] === $representative_id ? 'selected' : ''; ?>>
                                                <?php echo html_escape($representative['NOME']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="filter">
                                <label for="selectCliente">Cliente</label>
                                <select id="selectCliente" name="cliente">
                                    <option value="">Todos os clientes</option>
                                    <?php foreach ($filter_options['customers'] as $customer): ?>
                                        <option value="<?php echo (int) $customer['ID_FILTRO']; ?>"
                                            <?php echo (int) $customer['ID_FILTRO'] === $customer_id ? 'selected' : ''; ?>>
                                            <?php echo html_escape($customer['NOME']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="filter-date-range">
                                <div class="filter">
                                    <label for="dataIni">Período inicial</label>
                                    <input id="dataIni" name="data_ini" type="date"
                                        value="<?php echo html_escape($start_date); ?>" required>
                                </div>
                                <div class="filter">
                                    <label for="dataFim">Período final</label>
                                    <input id="dataFim" name="data_fim" type="date"
                                        value="<?php echo html_escape($end_date); ?>" required>
                                </div>
                            </div>
                            <p class="filter-help">Os filtros de representante, equipe e cliente são aplicados aos indicadores, gráficos, rankings e análise de clientes.</p>
                            <button type="submit" class="search-button">Pesquisar</button>
                        </form>
                    </details>
                </div>
            </details>
        </div>
    </header>

    <main class="dashboard-main">
        <?php if (count($errors) > 0): ?>
            <section class="feedback error-feedback" role="alert">
                <strong>Não foi possível exibir os dados.</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo html_escape($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </section>
        <?php elseif ($dashboard === NULL): ?>
            <section class="feedback">
                Nenhuma empresa está disponível para exibir o dashboard.
            </section>
        <?php else: ?>
            <section class="metric-grid" aria-label="Indicadores do período">
                <?php
                $metrics = array(
                    array('Total de Vendas', Functions::MoneyFormat($revenue), $formatChange($revenueChange), 'Faturamento total do período', 'money-icon'),
                    array('Quantidade de Vendas', Functions::IntegerFormat($salesCount), $formatChange($salesChange), 'Pedidos realizados no período', 'orders-icon'),
                    array('Ticket Médio', Functions::MoneyFormat($averageTicket), $formatChange($ticketChange), 'Valor médio por venda', 'ticket-icon'),
                    array('Clientes Ativos', Functions::IntegerFormat($activeCustomers), $formatChange($customersChange), 'Clientes que compraram no período', 'customers-icon')
                );
                foreach ($metrics as $metric):
                ?>
                    <article class="metric-card<?php
                        echo $metric[4] === 'customers-icon' ? ' metric-card-customers' : '';
                        echo in_array($metric[4], array('money-icon', 'orders-icon', 'ticket-icon'), true) ? ' metric-card-primary' : '';
                    ?>">
                        <div class="metric-heading">
                            <h2><?php echo html_escape($metric[0]); ?></h2>
                            <span class="metric-icon <?php echo html_escape($metric[4]); ?>" aria-hidden="true"></span>
                        </div>
                        <p class="metric-value"><?php echo html_escape($metric[1]); ?></p>
                        <p class="metric-change <?php echo html_escape($metric[2]['class']); ?>">
                            <?php echo html_escape($metric[2]['text']); ?>
                        </p>
                        <p class="metric-description"><?php echo html_escape($metric[3]); ?></p>
                    </article>
                <?php endforeach; ?>
            </section>

            <section class="panel sales-panel">
                <div class="section-heading">
                    <div>
                        <p class="section-kicker">Desempenho</p>
                        <h2>Vendas por dia</h2>
                        <p class="section-description">Comportamento diário do período selecionado</p>
                    </div>
                    <div class="view-switch" role="group" aria-label="Visualização das vendas diárias">
                        <button type="button" class="view-button is-active" data-view="chart">Gráfico</button>
                        <button type="button" class="view-button" data-view="table">Tabela</button>
                    </div>
                </div>
                <div id="dailyChart" class="daily-chart">
                    <?php if (count($dailySales) === 0): ?>
                        <p class="empty-state">Sem vendas no período selecionado.</p>
                    <?php else: ?>
                        <?php foreach ($dailySales as $dailySale):
                            $amount = (float) $dailySale['VALOR_TOTAL'];
                            $barHeight = $maxDailyRevenue > 0 ? max(4, ($amount / $maxDailyRevenue) * 100) : 0;
                        ?>
                            <div class="chart-column" title="<?php echo html_escape(Functions::DateFormat($dailySale['DATA_VENDA']) . ' · ' . Functions::MoneyFormat($amount)); ?>">
                                <span class="chart-bar" style="height: <?php echo $barHeight; ?>%"></span>
                                <span class="chart-label"><?php echo html_escape(date('d/m', strtotime($dailySale['DATA_VENDA']))); ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div id="dailyTable" class="table-scroll is-hidden">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Dia</th>
                                <th>Faturamento</th>
                                <th>Δ dia anterior</th>
                                <th>Pedidos</th>
                                <th>Ticket médio</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $previousDailyRevenue = NULL; ?>
                            <?php foreach ($dailySales as $dailySale):
                                $dailyRevenue = (float) $dailySale['VALOR_TOTAL'];
                                $dailyChange = $previousDailyRevenue > 0 ?
                                    (($dailyRevenue - $previousDailyRevenue) / $previousDailyRevenue) * 100 : NULL;
                                $dailyTicket = (int) $dailySale['QUANTIDADE_VENDAS'] > 0 ?
                                    $dailyRevenue / (int) $dailySale['QUANTIDADE_VENDAS'] : 0;
                            ?>
                                <tr>
                                    <td><?php echo html_escape(Functions::DateFormat($dailySale['DATA_VENDA'])); ?></td>
                                    <td><?php echo html_escape(Functions::MoneyFormat($dailyRevenue)); ?></td>
                                    <td><?php echo $dailyChange === NULL ? '—' : html_escape(number_format($dailyChange, 1, ',', '.') . '%'); ?></td>
                                    <td><?php echo (int) $dailySale['QUANTIDADE_VENDAS']; ?></td>
                                    <td><?php echo html_escape(Functions::MoneyFormat($dailyTicket)); ?></td>
                                </tr>
                            <?php $previousDailyRevenue = $dailyRevenue; endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="panel rankings-panel">
                <div class="section-heading">
                    <div>
                        <p class="section-kicker">🏆 Comparativo</p>
                        <h2>Rankings</h2>
                        <p class="section-description">Maiores desempenhos no período selecionado</p>
                    </div>
                </div>
                <div class="ranking-grid">
                    <?php
                    $rankingGroups = array(
                        'products' => 'Produtos mais vendidos',
                        'salespeople' => 'Top vendedores',
                        'suppliers' => 'Top fabricantes'
                    );
                    foreach ($rankingGroups as $key => $title):
                        $items = isset($rankings[$key]) ? array_slice($rankings[$key], 0, 5) : array();
                    ?>
                        <article class="ranking-card">
                            <h3><?php echo html_escape($title); ?></h3>
                            <?php if (count($items) === 0): ?>
                                <p class="empty-state">Sem dados para o período.</p>
                            <?php else: ?>
                                <ol>
                                    <?php foreach ($items as $index => $item): ?>
                                        <li>
                                            <span class="ranking-position"><?php echo $index + 1; ?></span>
                                            <span class="ranking-name"><?php echo html_escape($item['DESCRICAO']); ?></span>
                                            <span class="ranking-value"><?php echo html_escape(Functions::MoneyFormat($item['VALOR_TOTAL'])); ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ol>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="panel customer-panel">
                <div class="section-heading">
                    <div>
                        <p class="section-kicker">Carteira</p>
                        <h2>Análise de Clientes</h2>
                        <p class="section-description">Classificação por faturamento e ticket médio</p>
                    </div>
                </div>
                <div class="table-scroll">
                    <table class="data-table customer-table">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Faturamento</th>
                                <th>Pedidos</th>
                                <th>Ticket médio</th>
                                <th>Classificação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($customers) === 0): ?>
                                <tr><td colspan="5" class="empty-cell">Sem dados para o período.</td></tr>
                            <?php else: ?>
                                <?php foreach ($customers as $customer):
                                    $share = $customerRevenue > 0 ?
                                        ((float) $customer['VALOR_TOTAL'] / $customerRevenue) * 100 : 0;
                                    if ($share >= 10) {
                                        $classification = 'Excelente';
                                    } elseif ($share >= 5) {
                                        $classification = 'Muito Bom';
                                    } elseif ($share >= 2) {
                                        $classification = 'Bom';
                                    } elseif ($share >= 1) {
                                        $classification = 'Regular';
                                    } else {
                                        $classification = 'Baixo';
                                    }
                                ?>
                                    <tr>
                                        <td><?php echo html_escape($customer['CLIENTE_NOME']); ?></td>
                                        <td><?php echo html_escape(Functions::MoneyFormat($customer['VALOR_TOTAL'])); ?></td>
                                        <td><?php echo (int) $customer['QUANTIDADE_VENDAS']; ?></td>
                                        <td><?php echo html_escape(Functions::MoneyFormat($customer['TICKET_MEDIO'])); ?></td>
                                        <td><span class="classification-badge"><?php echo html_escape($classification); ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        <?php endif; ?>
    </main>

    <footer class="dashboard-footer">ForçaV · <?php echo date('Y'); ?></footer>
    <script src="<?php echo html_escape($appBasePath . '/js/mobile-dashboard.js?v=filters-menu-7'); ?>"></script>
</body>
</html>
