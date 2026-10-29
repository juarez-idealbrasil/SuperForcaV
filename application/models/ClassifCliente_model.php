<?php

  class ClassifCliente_model extends CI_Model
  {    
        
    function __construct() {
        parent::__construct();
    }

    function getClassifCliente($represId) {

      $sql = "SELECT * FROM SEL_CLASS_CLI(" . $represId . ")";

      $query = $this->db->query($sql);
      $rows = $query->result();

      $data=array();
      if ($query->num_rows()>0) {
        foreach($rows as $row) {
          array_push($data, array('Desconto'=>$row->DESCONTO, 'Id'=>$row->ID_CLA, 'TipoOper'=>$row->OPER_TYPE));
        }
      }
      return $data;
    }

  }
