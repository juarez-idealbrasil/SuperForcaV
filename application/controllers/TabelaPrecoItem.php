<?php

require(APPPATH.'libraries/REST_Controller.php');    

class TabelaPrecoItem extends REST_Controller{

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
    
        $this->load->model('TabelaPrecoItem_model');
        $this->load->model('InitializeReceb_model');
    }        
        
    public function index_get() {

        $id    = $this->get('represId');
        $table = 'ITEM_TAB_PRECO';

        $response = $this->InitializeReceb_model->execInitializeReceb( $id, $table );

        if ( $response['httpcode'] == 200 ) {
            $data = $this->TabelaPrecoItem_model->getTabelaPrecoItem($id);
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
