<?php

  class Cobranca_model extends CI_Model
  {
            
    function __construct() {
        parent::__construct();
    }

    function getCobranca($represId, $sincron) {

      $sql = "SELECT * FROM SEL_COBRANCA(" . $represId . "," . (int)$sincron . ")";

      $query = $this->db->query($sql);
      $rows = $query->result();

      $data=array();
      if ($query->num_rows()>0) {
        foreach($rows as $row) {
          array_push($data, array(
              'ClienteId'=>$row->ID_CLI, 
              'Id'=>$row->ID_COB,
              'NumeroDoc'=>$row->NUM_DOC,
              'Vencimento'=>$row->VENCTO,
              'Valor'=>$row->VALOR,
              'Pago'=>$row->PAGO,
              'FormaPagam'=>iconv("Windows-1252", "utf-8//IGNORE", $row->FORMA_PG),
              'TipoOper'=>$row->OPER_TYPE));
        }
      }
      return $data;
    }

  }
