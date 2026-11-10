<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class InovasseDashboard extends CI_Controller
{
    private $views = array(
        'faturamento' => array(
            'title' => 'Faturamento',
            'table' => 'VW_INOVASSE_FATURAMENTO',
            'date_column' => 'DATA'
        ),
        'cliente' => array(
            'title' => 'Cliente',
            'table' => 'VW_INOVASSE_CLIENTE'
        ),
        'estoque' => array(
            'title' => 'Estoque',
            'table' => 'VW_INOVASSE_ESTOQUE'
        ),
        'produtos' => array(
            'title' => 'Produtos',
            'table' => 'VW_INOVASSE_PRODUTO'
        )
    );

    public function __construct()
    {
        parent::__construct();
        $this->load->model('InovasseDashboard_model');
        $this->load->helper('url');
    }

    public function index()
    {
        $selectedView = $this->input->get('view', TRUE);
        $selectedView = $selectedView ? $selectedView : 'faturamento';
        $search = $this->input->get('search', TRUE) === '1';
        $startDate = $this->input->get('data_inicio', TRUE);
        $endDate = $this->input->get('data_fim', TRUE);

        $data = array(
            'views' => $this->views,
            'selected_view' => $selectedView,
            'data_inicio' => $startDate,
            'data_fim' => $endDate,
            'columns' => array(),
            'rows' => array(),
            'totals' => array(),
            'operation_totals' => array(),
            'operation_quantities' => array(),
            'canceled_summary' => NULL,
            'searched' => $search,
            'error' => NULL
        );

        if (!is_string($selectedView) || !isset($this->views[$selectedView])) {
            $data['error'] = 'A consulta selecionada não é válida.';
            $data['selected_view'] = 'faturamento';
        } elseif ($search) {
            if ($selectedView === 'faturamento') {
                if (!$this->isValidDate($startDate) || !$this->isValidDate($endDate)) {
                    $data['error'] = 'Informe datas de início e fim válidas para pesquisar o faturamento.';
                } elseif ($startDate > $endDate) {
                    $data['error'] = 'A data de início não pode ser posterior à data de fim.';
                }
            }

            if ($data['error'] === NULL) {
                $result = $this->InovasseDashboard_model->search(
                    $this->views[$selectedView],
                    $selectedView === 'faturamento' ? $startDate : NULL,
                    $selectedView === 'faturamento' ? $endDate : NULL
                );
                $data['columns'] = $result['columns'];
                $data['rows'] = $result['rows'];
                $data['totals'] = $result['totals'];
                $data['operation_totals'] = $result['operation_totals'];
                $data['operation_quantities'] = $result['operation_quantities'];
                $data['canceled_summary'] = $result['canceled_summary'];
                $data['error'] = $result['error'];
            }
        }

        $this->load->view('inovasse_dashboard', $data);
    }

    private function isValidDate($value)
    {
        if (!is_string($value) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return FALSE;
        }

        return checkdate((int) substr($value, 5, 2), (int) substr($value, 8, 2), (int) substr($value, 0, 4));
    }
}
