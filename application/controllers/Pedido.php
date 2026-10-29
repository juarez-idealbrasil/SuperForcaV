<?php

require(APPPATH.'libraries/REST_Controller.php');    

class Pedido extends REST_Controller{

    function __construct() {
        // Construct the parent class
        parent::__construct();
    
        $this->load->model('Pedido_model');
      
	    //$this->load->helper('log_manager_helper');
      
    }        
        
    public function index_post() {        

        $content = file_get_contents( 'php://input' );         

      //LogManager::Message( json_encode ( $content ) );

        $response =  $this->Pedido_model->saveList( $content );

        if ($response['httpcode'] == 200) {
            $this->response($response, REST_Controller::HTTP_OK);
        }else{
            $this->response($response, REST_Controller::HTTP_NOT_FOUND);
        }

    }        
        
} 
