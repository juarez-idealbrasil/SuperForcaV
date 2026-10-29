<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {

    function __construct() {
        // Construct the parent class
        parent::__construct();
    
        $this->load->model('Report/OutrosComissao_model');
        $this->load->model('Report/OutrosMKTControl_model');
        $this->load->model('Report/DashboardCobranca_model');
        $this->load->model('Report/DashboardClientesNPosit_model');
        $this->load->model('Report/DashboardObjetivo_model');
        $this->load->model('ConfigReportFV_model');
		
    }

    // Comissão 
    private function getComissao() {

        $response = $this->ConfigReportFV_model->getConfigReportFV( 'COMISSAO' );		

        $idRepres = $_GET['id_repres'];
        $idEquipe = $_GET['id_equipe'];
        $dataIni  = $response['data_ini'];
        $dataFim  = $response['data_fim'];
        $mode     = $response['modo'];

        return $this->OutrosComissao_model->get( $idRepres, $idEquipe, $dataIni, $dataFim, $mode );        

    }

    // Cobrança
    private function getCobranca() {

        $idRepres = $_GET['id_repres'];
        $idEquipe = $_GET['id_equipe'];

        return $this->DashboardCobranca_model->get( $idRepres, $idEquipe );

    }

    // Controle MKT
    private function getMKTControl() {

        $response = $this->ConfigReportFV_model->getConfigReportFV( 'CONTROL_MKT' );

        $idRepres = $_GET['id_repres'];
        $idEquipe = $_GET['id_equipe'];
        $dataIni  = $response['data_ini'];
        $dataFim  = $response['data_fim'];
        $mode     = $response['modo'];

        return $this->OutrosMKTControl_model->get( $idRepres, $idEquipe, $dataIni, $dataFim, $mode );

    }

    // Quantidade de clientes não positivados
    private function getClientesNPosit() {

        $idRepres = $_GET['id_repres'];

        return $this->DashboardClientesNPosit_model->get( $idRepres );

    }

    // Objetivo
    private function getObjetivo() {

        $dataFim  = new DateTime('now');
        $idRepres = $_GET['id_repres'];
        $idEquipe = $_GET['id_equipe'];
        $dataIni  = new DateTime('first day of this month');
        $dataIni  = $dataIni->format('Y-m-d');
        $dataFim  = $dataFim->modify('last day of this month');
        $dataFim  = $dataFim->format('Y-m-d');
        $mode     = 'valor';

        return $this->DashboardObjetivo_model->get( $idRepres, $idEquipe, $dataIni, $dataFim, $mode );

    }

	public function index()	{

        $objetivo = $this->getObjetivo();

        $data['id_repres']     = $_GET['id_repres'];
        $data['id_equipe']     = $_GET['id_equipe'];
        $data['months']        = 3;
        $data['comissao']      = $this->getComissao();
        $data['mkt']           = $this->getMKTControl();
        $data['cobranca']      = $this->getCobranca();
        $data['cliente']       = $this->getClientesNPosit();
        $data['objetivo']      = $objetivo['Objetivo'];
        $data['realizado']     = $objetivo['Realizado'];
        $data['realizadoPerc'] = $objetivo['RealizadoPerc'];
        
        $this->load->view('Dashboard', $data);

	}
}
