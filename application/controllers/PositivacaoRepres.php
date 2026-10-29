<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class PositivacaoRepres extends CI_Controller {

    function __construct() {
        // Construct the parent class
        parent::__construct();
    
        $this->load->model('Report/PositivacaoRepres_model');		
    }        	

	public function index()	{
		
        $idRepres = $_GET['id_repres'];
        $idEquipe = $_GET['id_equipe'];
        $dataIni  = $_GET['data_ini'];
        $dataFim  = $_GET['data_fim'];

        $data = $this->PositivacaoRepres_model->get( $idRepres, $idEquipe, $dataIni, $dataFim );

        $this->load->view('PositivacaoRepres', $data);

	}
}
