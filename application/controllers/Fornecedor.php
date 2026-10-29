<?php

require(APPPATH.'libraries/REST_Controller.php');    

class Fornecedor extends REST_Controller{

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
    
        $this->load->model('Fornecedor_model');
        $this->load->model('InitializeReceb_model');
    }        
        
    public function index_get() {

        $id    = $this->get('represId');
        $table = 'FORNECEDOR';

        $response = $this->InitializeReceb_model->execInitializeReceb( $id, $table );

        if ( $response['httpcode'] == 200 ) {
            $data = $this->Fornecedor_model->getFornecedor($id);
            $this->response($data, REST_Controller::HTTP_OK);
        }else{
            $this->response($response, REST_Controller::HTTP_NOT_FOUND);
        }       
    }
        
    public function by_get() {
       $this->index_get();
    }  
        
} 

?>
