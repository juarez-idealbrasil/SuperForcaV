<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class HomeView extends CI_Controller{

    function __construct() {
        // Construct the parent class
        parent::__construct();

        $this->index();
    
        //$this->load->model('Report/ResumoVenda_model');
    }        
        
    public function index() {

        /*$idRepres = $this->get('id_repres');
        $idEquipe = $this->get('id_equipe');
        $dataIni  = $this->get('data_ini');
        $dataFim  = $this->get('data_fim');

        $data['body'] = $this->ResumoVenda_model->get( $idRepres, $idEquipe, $dataIni, $dataFim );*/        

        $this->load->view('Home_view');

    }       
        
} 