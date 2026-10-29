<?php

require(APPPATH.'libraries/REST_Controller.php');    

class LoteProduto extends REST_Controller{

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
    
        $this->load->model('LoteProduto_model');
    }        
        
    public function index_get() {

        $produtoId      = $this->get('produtoId');     

        $data = $this->LoteProduto_model->getLoteProduto($produtoId);
        $this->response($data, REST_Controller::HTTP_OK);     
    }
        
    public function by_get() {
       $this->index_get();
    }        
} 

?>
