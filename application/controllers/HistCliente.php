<?php

require(APPPATH.'libraries/REST_Controller.php');    

class HistCliente extends REST_Controller{

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
    
        $this->load->model('HistCliente_model');
    }        
        
    public function index_get() {

        $clienteId      = $this->get('clienteId');     
        $respId      = $this->get('respId');   

        $data = $this->HistCliente_model->getHistCliente($clienteId, $respId);
        $this->response($data, REST_Controller::HTTP_OK);     
    }
        
    public function by_get() {
       $this->index_get();
    }  

    public function index_post() {        

        $content = file_get_contents( 'php://input' );         

        $response =  $this->HistCliente_model->saveList( $content );

        if ($response['httpcode'] == 200) {
            $this->response($response, REST_Controller::HTTP_OK);
        }else{
            $this->response($response, REST_Controller::HTTP_NOT_FOUND);
        }

    } 
        
} 

?>
