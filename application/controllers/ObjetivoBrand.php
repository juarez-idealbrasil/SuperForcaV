<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ObjetivoBrand extends CI_Controller {

    function __construct() {
        // Construct the parent class
        parent::__construct();
    
        $this->load->model('Report/ObjetivoBrand_model');
        $this->load->model('ConfigReportFV_model');
    }        	

	public function index()	{
		
        $response = $this->ConfigReportFV_model->getConfigReportFV( 'OBJ_BRAND' );

        $idRepres = $_GET['id_repres'];
        $idEquipe = $_GET['id_equipe'];
        $dataIni  = $_GET['data_ini'];
        $dataFim  = $_GET['data_fim'];
        $mode     = $_GET['report_mode'];

        $data = $this->ObjetivoBrand_model->get( $idRepres, $idEquipe, $dataIni, $dataFim, $mode );

        $this->load->view('ObjetivoBrand', $data);

	}
}
