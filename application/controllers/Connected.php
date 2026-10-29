<?php

require(APPPATH.'libraries/REST_Controller.php');    

class Connected extends REST_Controller{

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
    
        $this->load->model('Connected_model');
    }        
        
    public function index_get() {
        
        $data = $this->Connected_model->getConnected();
        
        if ($data == false) {
            $data = array( 'ok'=>'false', 'msg' => $Connected_model->$ERRO_CONEXAO );
            $this->response($data, REST_Controller::HTTP_NOT_FOUND);
        } else {
            $this->response($data, REST_Controller::HTTP_OK);
        }
    }
        
    public function by_get() {
       $this->index_get();
    }  
        
} 

?>