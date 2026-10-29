<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class PositivacaoBrand extends CI_Controller {

    function __construct() {
        // Construct the parent class
        parent::__construct();
    
        $this->load->model('Report/PositivacaoBrand_model');		
    }        	

	public function index()	{		

        $idRepres = $_GET['id_repres'];
        $idEquipe = $_GET['id_equipe'];
        $dataIni  = $_GET['data_ini'];
        $dataFim  = $_GET['data_fim'];

        $data = $this->PositivacaoBrand_model->get( $idRepres, $idEquipe, $dataIni, $dataFim);		

        $this->load->view('PositivacaoBrand', $data);

	}
}
