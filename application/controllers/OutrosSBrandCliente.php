<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class OutrosSBrandCliente extends CI_Controller {

    function __construct() {
        // Construct the parent class
        parent::__construct();
    
        $this->load->model('Report/OutrosSBrandCliente_model');
        $this->load->model('ConfigReportFV_model');
    }        	

	public function index()	{
	
        $response = $this->ConfigReportFV_model->getConfigReportFV( 'CLI_X_SUB_BRAND' );

        $idRepres = $_GET['id_repres'];
        $idEquipe = $_GET['id_equipe'];
        $dataIni  = $_GET['data_ini'];
        $dataFim  = $_GET['data_fim'];
        $mode     = $response['report_mode'];

        $data['body'] = $this->OutrosSBrandCliente_model->get( $idRepres, $idEquipe, $dataIni, $dataFim, $mode );

        $this->load->view('OutrosSBrandCliente', $data);

	}
}
