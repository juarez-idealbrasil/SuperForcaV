<?php

  class TabelaPreco_model extends CI_Model
  {    
        
    function __construct() {
        parent::__construct();
    }

    function getTabelaPreco($represId) {

      $sql = "SELECT * FROM SEL_TAB_PRECO(" . $represId . ")";

      $query = $this->db->query($sql);
      $rows = $query->result();

      $data=array();
      if ($query->num_rows()>0) {
        foreach($rows as $row) {
          array_push($data, array(
            'Id'=>$row->ID_TAB, 
            'Inativo'=>$row->INATIVO, 
            'DataInicio'=>$row->DT_INICIO, 
            'DataFim'=>$row->DT_FIM, 
            'UsePrecoBase'=>$row->USE_PRECO_BASE,             
            'TipoOper'=>$row->OPER_TYPE));
        }
      }
      return $data;
    }

  }
