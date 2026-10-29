<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ResumoVenda extends CI_Controller {

    function __construct() {
        // Construct the parent class
        parent::__construct();
    
        $this->load->model('Report/ResumoVenda_model');
    }        	

	public function index()	{
		
        $idRepres = $_GET['id_repres'];
        $idEquipe = $_GET['id_equipe'];
        $dataIni  = $_GET['data_ini'];
        $dataFim  = $_GET['data_fim'];

        $data = $this->ResumoVenda_model->get( $idRepres, $idEquipe, $dataIni, $dataFim );

        $this->load->view('ResumoVenda', $data);

	}
}
