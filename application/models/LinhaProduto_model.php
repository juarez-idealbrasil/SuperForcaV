<?php

  class LinhaProduto_model extends CI_Model
  {    
        
    function __construct() {
        parent::__construct();
    }

    function getLinhaProduto($represId) {

      $sql = "SELECT * FROM SEL_LINHA_PROD(" . $represId . ")";

      $query = $this->db->query($sql);
      $rows = $query->result();

      $data=array();
      if ($query->num_rows()>0) {
        foreach($rows as $row) {
          array_push($data, array(
            'Descricao'=>iconv("Windows-1252", "utf-8//IGNORE", $row->DESCRICAO), 
            'Id'=>$row->ID_LIN, 
            'TipoOper'=>$row->OPER_TYPE));
        }
      }
      return $data;
    }

  }
