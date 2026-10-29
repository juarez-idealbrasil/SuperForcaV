<?php

  class TabelaPrecoItem_model extends CI_Model
  {
            
    function __construct() {
        parent::__construct();
    }

    function getTabelaPrecoItem($represId) {

      $sql = "SELECT * FROM SEL_ITEM_TAB_PRECO(" . $represId . ")";

      $query = $this->db->query($sql);
      $rows = $query->result();

      $data=array();
      if ($query->num_rows()>0) {
        foreach($rows as $row) {
          array_push($data, array(
            'Id'=>$row->ID_ITEM, 
            'TabPrecoId'=>$row->ID_TAB, 
            'ProdutoId'=>$row->ID_PRO, 
            'PrecoBase'=>$row->PRECO_BASE,
            'Ajuste'=>$row->AJUSTE,
            'TipoOper'=>$row->OPER_TYPE));
        }
      }
      return $data;
    }

  }
