<?php

require(APPPATH.'libraries/REST_Controller.php');    

class ForcaVWS extends REST_Controller{

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
    
        $this->load->model('Connected_model');
    }        
        
    public function about_get() {
        
        $data = array( 'Organization'     =>'Ideal Brasil Sistemas', 
                       'Application'      => 'Força de Venda',
                       'Current Version'  => '6.0.22',
                       'Version 1.0.2'    => 'Possivel inserir dados da nova versão do FV remotamente executando a url host:porta/forcavws/index.php/ForcaVRelease/set_release',
                       'Version 1.0.1'    => 'Ajustes',
                       'Version 1.0.0'    => 'Inicio do projeto. Alterado o Web Service de IIS para Apache'
                      );

        $this->response($data, REST_Controller::HTTP_OK);

    }

}    
