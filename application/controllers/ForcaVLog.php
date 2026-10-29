<?php

require(APPPATH.'libraries/REST_Controller.php');    

class ForcaVLog extends REST_Controller{

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
    
        $this->load->model('ForcaVLog_model');
    }                

    /*http://localhost:2222/ForcaVWS/index.php/ForcaVLog?represId=1&date_sent=2019/12/10*/        
    public function index_get() {

        $id       = $this->get('represId');
        $dateSent = date( 'Y-m-d', strtotime( $this->get('date_sent') ) );

        $data = $this->ForcaVLog_model->getLog($id, $dateSent);
        $this->response($data, REST_Controller::HTTP_OK);
        
    }

    /*
        localhost:2222/forcavws/index.php/ForcaVLog/
        {"versionFV":604,
         "represId":15,
         "text": lorium pratinum
         }
    */
    public function index_post() {

        $content = file_get_contents( 'php://input' );

        $response =  $this->ForcaVLog_model->setLog( $content );

        if ($response['httpcode'] == 200) {
            $this->response($response, REST_Controller::HTTP_OK);
        }else{
            $this->response($response, REST_Controller::HTTP_NOT_FOUND);
        }
        
    }     
        
} 