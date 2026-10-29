<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class OutrosComissao extends CI_Controller {

    function __construct() {
        // Construct the parent class
        parent::__construct();
    
        $this->load->model('Report/OutrosComissao_model');
        $this->load->model('ConfigReportFV_model');
    }        	

	public function index()	{		

        $response = $this->ConfigReportFV_model->getConfigReportFV( 'COMISSAO' );

        $idRepres = $_GET['id_repres'];
        $idEquipe = $_GET['id_equipe'];
        $dataIni  = $response['data_ini'];
        $dataFim  = $response['data_fim'];
        $mode     = $response['report_mode'];

        $data['body'] = $this->OutrosComissao_model->get( $idRepres, $idEquipe, $dataIni, $dataFim, $mode );

        $this->load->view('OutrosComissao', $data);

	}
}
