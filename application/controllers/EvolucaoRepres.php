<?php
defined('BASEPATH') OR exit('No direct script access alloweduuu');

class EvolucaoRepres extends CI_Controller {

    function __construct() {
        // Construct the parent class
        parent::__construct();
    
        $this->load->model('Report/EvolucaoRepres_model');
        $this->load->model('ConfigReportFV_model');
    }        	

	public function index()	{

        $data['body'] = $this->get();

        $this->load->view('EvolucaoRepres', $data);

	}

    public function get() {

        $response = $this->ConfigReportFV_model->getConfigReportFV( 'EVOL_PER_REP' );

        $idRepres = $_GET['id_repres'];
        $idEquipe = $_GET['id_equipe'];
        $dataIni  = $response['data_ini'];
        $dataFim  = $response['data_fim'];
        $mode     = $response['report_mode'];

        return $this->EvolucaoRepres_model->get( $idRepres, $idEquipe, $dataIni, $dataFim, $mode );

    }
}
