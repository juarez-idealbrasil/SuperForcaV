<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ClienteNPositivado extends CI_Controller {

    function __construct() {
        // Construct the parent class
        parent::__construct();
    
        $this->load->model('Report/ClienteNPositivado_model');
    }        	

	public function index()	{

        $idRepres = $_GET['id_repres'];
        $months   = $_GET['months'];

        $data = $this->ClienteNPositivado_model->get( $idRepres, $months );

        $this->load->view('ClienteNPositivado', $data);

	}
}
