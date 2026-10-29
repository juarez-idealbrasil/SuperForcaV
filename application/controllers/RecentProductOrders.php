<?php

require(APPPATH.'libraries/REST_Controller.php');    

class RecentProductOrders extends REST_Controller{

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
    
        $this->load->model('RecentProductOrders_model');
    }        
        
    public function index_get() {

        $clientId  = $this->get('clientId');
        $productId = $this->get('productId');

        $data = $this->RecentProductOrders_model->getRecentProductOrders($clientId, $productId);
       
        $this->response($data, REST_Controller::HTTP_OK);        
        
    }
        
    public function by_get() {
       $this->index_get();
    }  
        
} 

?>
