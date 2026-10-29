<?php

require(APPPATH.'libraries/REST_Controller.php');    

class ForcaVRelease extends REST_Controller{

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
    
        $this->load->model('ForcaVRelease_model');
    }        
        
    /*http://localhost:2222/ForcaVWS/index.php/ForcaVRelease?represId=1&selectAll=0*/        
    public function index_get() {

        $id        = $this->get('represId');
        $selectAll = filter_var($this->get('selectAll'), FILTER_VALIDATE_BOOLEAN);

        $data = $this->ForcaVRelease_model->getRelease($id, $selectAll);
        $this->response($data, REST_Controller::HTTP_OK);
        
    }

    /*
        localhost:2222/forcavws/index.php/ForcaVRelease/set_release
        {"version":604,
         "represId":15}        
    */
    public function index_post() {

        $content = file_get_contents( 'php://input' );

        $response =  $this->ForcaVRelease_model->setFVVersion( $content );

        if ($response['httpcode'] == 200) {
            $this->response($response, REST_Controller::HTTP_OK);
        }else{
            $this->response($response, REST_Controller::HTTP_NOT_FOUND);
        }
        
    }

    /*
        localhost:2222/forcavws/index.php/ForcaVRelease/set_release
        {"FVVersionNext":604,
         "FVVersionLink":"https://www.idealbrasil.net/ForcaV6.0.4.apk",
         "FVVersionNote":"Alteracao no bla bla bla" ,
         "FVVersionLength":8666379, 
         "IdRepres":15}        
    */
    public function set_release_post() {

        $content = file_get_contents( 'php://input' );

        $response =  $this->ForcaVRelease_model->setRelease( $content );

        if ($response['httpcode'] == 200) {
            $this->response($response, REST_Controller::HTTP_OK);
        }else{
            $this->response($response, REST_Controller::HTTP_NOT_FOUND);
        }
        
    }        
        
} 