<?php

class LoteProduto_model extends CI_Model
{    
      
  function __construct() {
      parent::__construct();
  }

  function getLoteProduto($produtoId) {

    $sql = "SELECT * FROM SEL_LOTE_PRODUTO(" . $produtoId . ")";

    $query = $this->db->query($sql);
    $rows = $query->result();

    $data=array();
    if ($query->num_rows()>0) {
      foreach($rows as $row) {
        array_push($data, array(
            'Lote'=>$row->LOTE, 
            'Validade'=>$row->VALIDADE, 
            'Fabricacao'=>$row->FABRICACAO));
      }
    }
    return $data;
  }
}
