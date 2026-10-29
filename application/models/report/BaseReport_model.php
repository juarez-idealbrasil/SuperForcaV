<?php

  class BaseReport_model extends CI_Model {

    private $mConfigSys;
            
    function __construct() {
      parent::__construct();
      $this->load->model('config/ConfigSys_model');
      $this->mConfigSys = $this->ConfigSys_model->get();
    }

    public function get() {
      return $this->mConfigSys;
    }

  }
