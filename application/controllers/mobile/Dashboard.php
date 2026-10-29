<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->helper('mobile');

        if (!$this->session->userdata('mobile_user_id')) {
            mobile_redirect('mobile/auth');
        }

        $this->load->model('MobileDashboard_model');
        $this->load->model('MobileOrder_model');
    }

    public function index()
    {
        $today = new DateTime('today');
        $firstDay = new DateTime('first day of this month');

        try {
            $companies = $this->MobileDashboard_model->getCompanies();
        } catch (RuntimeException $exception) {
            show_error('Não foi possível carregar as empresas para o dashboard.', 500);
            return;
        }

        try {
            $filterOptions = $this->MobileDashboard_model->getFilterOptions();
        } catch (RuntimeException $exception) {
            show_error('Não foi possível carregar as opções dos filtros.', 500);
            return;
        }

        $companyId = $this->input->get('empresa');
        $startDate = $this->input->get('data_ini');
        $endDate = $this->input->get('data_fim');
        $errors = array();
        $dashboard = NULL;
        $representativeId = $this->getSelectedFilterId(
            'representante',
            $filterOptions['representatives'],
            'representante',
            $errors
        );
        $teamId = $this->getSelectedFilterId(
            'equipe',
            $filterOptions['teams'],
            'equipe',
            $errors
        );
        $customerId = $this->getSelectedFilterId(
            'cliente',
            $filterOptions['customers'],
            'cliente',
            $errors
        );

        if ($companyId === NULL || $companyId === '') {
            $companyId = count($companies) > 0 ? (int) $companies[0]['ID_EMP'] : NULL;
        } elseif (!is_string($companyId) || filter_var($companyId, FILTER_VALIDATE_INT) === FALSE) {
            $errors[] = 'Selecione uma empresa válida.';
            $companyId = count($companies) > 0 ? (int) $companies[0]['ID_EMP'] : NULL;
        } else {
            $companyId = (int) $companyId;
        }

        $startDate = ($startDate === NULL || $startDate === '') ?
            $firstDay->format('Y-m-d') : $startDate;
        $endDate = ($endDate === NULL || $endDate === '') ?
            $today->format('Y-m-d') : $endDate;

        if (!$this->isValidDate($startDate) || !$this->isValidDate($endDate)) {
            $errors[] = 'Informe um período válido.';
            $startDate = $this->isValidDate($startDate) ? $startDate : $firstDay->format('Y-m-d');
            $endDate = $this->isValidDate($endDate) ? $endDate : $today->format('Y-m-d');
        } elseif ($startDate > $endDate) {
            $errors[] = 'A data inicial não pode ser posterior à data final.';
        }

        $companyIsAvailable = FALSE;
        foreach ($companies as $company) {
            if ((int) $company['ID_EMP'] === $companyId) {
                $companyIsAvailable = TRUE;
                break;
            }
        }

        if ($companyId !== NULL && !$companyIsAvailable) {
            $errors[] = 'A empresa selecionada não está disponível.';
        }

        if (count($errors) === 0 && $companyId !== NULL) {
            try {
                $dashboard = $this->MobileDashboard_model->getDashboard(
                    $companyId,
                    $startDate,
                    $endDate,
                    $representativeId,
                    $teamId,
                    $customerId
                );
            } catch (RuntimeException $exception) {
                $errors[] = 'Não foi possível carregar os indicadores. Tente novamente.';
            }
        }

        $data = array(
            'user_name' => $this->session->userdata('mobile_user_name'),
            'companies' => $companies,
            'filter_options' => $filterOptions,
            'representative_id' => $representativeId,
            'team_id' => $teamId,
            'customer_id' => $customerId,
            'company_id' => $companyId,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'dashboard' => $dashboard,
            'errors' => $errors
        );

        $this->load->view('mobile/dashboard', $data);
    }

    public function pedidos()
    {
        try {
            $orderToken = $this->getOrderToken();
        } catch (Exception $exception) {
            log_message('error', 'Mobile order token generation failed: ' . $exception->getMessage());
            show_error('Não foi possível iniciar um pedido neste momento.', 500);
            return;
        }

        $data = array(
            'user_name' => $this->session->userdata('mobile_user_name'),
            'order_token' => $orderToken
        );

        $this->load->view('mobile/pedidos', $data);
    }

    public function pedidos_clientes()
    {
        $term = $this->input->get('q');
        if (!is_string($term) || strlen(trim($term)) < 2) {
            $this->respondJson(array('items' => array()), 200);
            return;
        }

        try {
            $customers = $this->MobileOrder_model->searchCustomers(trim($term));
            $items = array_map(function ($customer) {
                return array(
                    'id' => (int) $customer['ID_PES'],
                    'name' => $customer['RAZ_SOC'],
                    'alias' => $customer['FANTASIA'],
                    'document' => $customer['CNPJ_CPF'],
                    'representative_id' => (int) $customer['ID_REP'],
                    'representative_name' => $customer['REPRESENTATIVE_NAME']
                );
            }, $customers);
            $this->respondJson(array('items' => $items), 200);
        } catch (RuntimeException $exception) {
            $this->respondJson(
                array('message' => 'Não foi possível buscar os clientes. Tente novamente.'),
                500
            );
        }
    }

    public function pedidos_produtos()
    {
        $customerId = $this->input->get('cliente');
        $term = $this->input->get('q');
        if (!is_string($customerId) || filter_var($customerId, FILTER_VALIDATE_INT) === FALSE
            || (int) $customerId < 1 || !is_string($term) || strlen(trim($term)) < 2) {
            $this->respondJson(array('items' => array()), 200);
            return;
        }

        try {
            $customer = $this->MobileOrder_model->getCustomer((int) $customerId);
            if ($customer === NULL) {
                $this->respondJson(array('message' => 'O cliente selecionado não está disponível.'), 404);
                return;
            }

            $products = $this->MobileOrder_model->searchProducts($customer, trim($term));
            $items = array_map(function ($product) {
                return array(
                    'id' => (int) $product['ID_PRO'],
                    'description' => $product['DESCRICAO'],
                    'barcode' => $product['BAR_CODE'],
                    'price' => (float) $product['PRECO']
                );
            }, $products);
            $this->respondJson(array('items' => $items), 200);
        } catch (DomainException $exception) {
            $this->respondJson(array('message' => $exception->getMessage()), 422);
        } catch (RuntimeException $exception) {
            $this->respondJson(
                array('message' => 'Não foi possível buscar os produtos. Tente novamente.'),
                500
            );
        }
    }

    public function pedidos_pagamentos()
    {
        $customerId = $this->input->get('cliente');
        if (!is_string($customerId) || filter_var($customerId, FILTER_VALIDATE_INT) === FALSE
            || (int) $customerId < 1) {
            $this->respondJson(array('message' => 'Selecione um cliente válido.'), 422);
            return;
        }

        try {
            $customer = $this->MobileOrder_model->getCustomer((int) $customerId);
            if ($customer === NULL) {
                $this->respondJson(array('message' => 'O cliente selecionado não está disponível.'), 404);
                return;
            }

            $this->respondJson(
                $this->MobileOrder_model->getPaymentOptions($customer),
                200
            );
        } catch (RuntimeException $exception) {
            $this->respondJson(
                array('message' => 'Não foi possível carregar as opções de pagamento. Tente novamente.'),
                500
            );
        }
    }

    public function pedidos_salvar()
    {
        if ($this->input->method(TRUE) !== 'POST') {
            show_404();
            return;
        }

        $token = $this->input->post('order_token', FALSE);
        if (!is_string($token) || $token === '') {
            $this->respondJson(array('message' => 'A sessão do pedido expirou. Atualize a tela.'), 403);
            return;
        }

        $previousToken = $this->session->userdata('mobile_order_previous_token');
        if (is_string($previousToken) && hash_equals($previousToken, $token)) {
            $this->respondJson(array(
                'order_id' => (int) $this->session->userdata('mobile_order_previous_id'),
                'duplicate' => TRUE
            ), 200);
            return;
        }

        $activeToken = $this->session->userdata('mobile_order_token');
        if (!is_string($activeToken) || !hash_equals($activeToken, $token)) {
            $this->respondJson(array('message' => 'A sessão do pedido expirou. Atualize a tela.'), 403);
            return;
        }

        $customerId = $this->input->post('customer_id');
        $conditionId = $this->input->post('condition_id');
        $paymentMethodId = $this->input->post('payment_method_id');
        $itemsJson = $this->input->post('items', FALSE);
        if (!is_string($customerId) || filter_var($customerId, FILTER_VALIDATE_INT) === FALSE
            || (int) $customerId < 1
            || !is_string($conditionId) || filter_var($conditionId, FILTER_VALIDATE_INT) === FALSE
            || (int) $conditionId < 1
            || !is_string($paymentMethodId) || filter_var($paymentMethodId, FILTER_VALIDATE_INT) === FALSE
            || (int) $paymentMethodId < 1) {
            $this->respondJson(array('message' => 'Confira o cliente e os dados de pagamento.'), 422);
            return;
        }

        $items = $this->decodeOrderItems($itemsJson);
        if ($items === NULL) {
            $this->respondJson(array('message' => 'Adicione produtos e confira as quantidades.'), 422);
            return;
        }

        try {
            $nextToken = $this->generateOrderToken();
        } catch (Exception $exception) {
            log_message('error', 'Mobile order token generation failed: ' . $exception->getMessage());
            $this->respondJson(
                array('message' => 'Não foi possível iniciar o salvamento do pedido. Tente novamente.'),
                500
            );
            return;
        }

        try {
            $orderHash = hash(
                'sha256',
                'mobile-web-order|' . (int) $this->session->userdata('mobile_user_id') . '|' . $token
            );
            $result = $this->MobileOrder_model->createOrder(
                (int) $customerId,
                $items,
                (int) $conditionId,
                (int) $paymentMethodId,
                $orderHash
            );

            $this->session->set_userdata(array(
                'mobile_order_previous_token' => $token,
                'mobile_order_previous_id' => $result['id'],
                'mobile_order_token' => $nextToken
            ));
            $this->respondJson(array(
                'order_id' => (int) $result['id'],
                'total' => isset($result['total']) ? $result['total'] : NULL,
                'duplicate' => (bool) $result['duplicate']
            ), 200);
        } catch (DomainException $exception) {
            $this->respondJson(array('message' => $exception->getMessage()), 422);
        } catch (RuntimeException $exception) {
            $this->respondJson(
                array('message' => 'Não foi possível salvar o pedido. Nenhum pedido foi confirmado; tente novamente.'),
                500
            );
        }
    }

    private function decodeOrderItems($itemsJson)
    {
        if (!is_string($itemsJson)) {
            return NULL;
        }

        $decoded = json_decode($itemsJson, TRUE);
        if (!is_array($decoded) || count($decoded) < 1 || count($decoded) > 50) {
            return NULL;
        }

        $items = array();
        $seenProducts = array();
        foreach ($decoded as $item) {
            if (!is_array($item)) {
                return NULL;
            }
            $quantity = isset($item['quantity']) && is_numeric($item['quantity'])
                ? (float) $item['quantity'] : 0;
            if (!isset($item['id'], $item['quantity'], $item['price'])
                || filter_var($item['id'], FILTER_VALIDATE_INT) === FALSE
                || (int) $item['id'] < 1
                || !is_numeric($item['quantity'])
                || !is_numeric($item['price'])
                || !is_finite($quantity)
                || !is_finite((float) $item['price'])
                || $quantity < 0.001
                || $quantity > 100000
                || abs($quantity - round($quantity, 3)) > 0.0000001
                || (float) $item['price'] <= 0) {
                return NULL;
            }

            $productId = (int) $item['id'];
            if (isset($seenProducts[$productId])) {
                return NULL;
            }

            $seenProducts[$productId] = TRUE;
            $items[] = array(
                'id' => $productId,
                'quantity' => $quantity,
                'price' => (float) $item['price']
            );
        }

        return $items;
    }

    private function getOrderToken()
    {
        $token = $this->session->userdata('mobile_order_token');
        if (!is_string($token) || strlen($token) !== 48) {
            $token = $this->generateOrderToken();
            $this->session->set_userdata('mobile_order_token', $token);
        }

        return $token;
    }

    private function generateOrderToken()
    {
        return bin2hex(random_bytes(24));
    }

    private function respondJson($data, $status)
    {
        $this->output
            ->set_status_header($status)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    private function getSelectedFilterId($parameter, $options, $label, &$errors)
    {
        $value = $this->input->get($parameter);
        if ($value === NULL || $value === '') {
            return 0;
        }

        $id = filter_var($value, FILTER_VALIDATE_INT);
        if ($id === FALSE || $id < 1) {
            $errors[] = 'O filtro de ' . $label . ' é inválido.';
            return 0;
        }

        foreach ($options as $option) {
            if ((int) $option['ID_FILTRO'] === (int) $id) {
                return (int) $id;
            }
        }

        $errors[] = 'O filtro de ' . $label . ' não está disponível.';
        return 0;
    }

    private function isValidDate($value)
    {
        if (!is_string($value) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return FALSE;
        }

        $date = DateTime::createFromFormat('!Y-m-d', $value);
        return $date !== FALSE && $date->format('Y-m-d') === $value;
    }
}
