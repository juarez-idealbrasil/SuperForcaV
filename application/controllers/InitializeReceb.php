<?php

require(APPPATH.'libraries/REST_Controller.php');    

class InitializeReceb extends REST_Controller{

    function __construct() {
        // Construct the parent class
        parent::__construct();
    
        $this->load->model('InitializeReceb_model');
    }        
        
    public function index_post() {

        $id     = $this->post( 'id' );
        $table  = $this->post( 'tableName' );

        $response = $this->InitializeReceb_model->execInitializeReceb( $id, $table );        

        if ( $response['httpcode'] == 200 ) {
            $this->response( $response, REST_Controller::HTTP_OK );
        }else{
            $this->response($response, REST_Controller::HTTP_NOT_FOUND);
        }

    }        
        
} 

?>
