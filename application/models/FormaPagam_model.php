<?php

  class FormaPagam_model extends CI_Model
  {    
        
    function __construct() {
        parent::__construct();
    }

    function getFormaPagam($represId) {

      $sql = "SELECT * FROM SEL_FORMA_PG(" . $represId . ")";

      $query = $this->db->query($sql);
      $rows = $query->result();

      $data=array();
      if ($query->num_rows()>0) {
        foreach($rows as $row) {
          array_push($data, array(
            'Descricao'=>iconv("Windows-1252", "utf-8//IGNORE", $row->DESCRICAO), 
            'Id'=>$row->ID_FPG, 
            'Inativo'=>$row->INATIVO, 
            'AllowSell'=>$row->ALLOW_SELL_FV, 
            'IndPrazo'=>$row->IND_PAGAM_PRAZO, 
            'TipoOper'=>$row->OPER_TYPE));
        }
      }
      return $data;
    }

  }
