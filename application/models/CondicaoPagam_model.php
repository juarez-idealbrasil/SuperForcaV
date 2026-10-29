<?php

  class CondicaoPagam_model extends CI_Model
  {   
        
    function __construct() {
        parent::__construct();
    }

    function getCondicaoPagam($represId) {

      $sql = "SELECT * FROM SEL_COND_PG(" . $represId . ")";

      $query = $this->db->query($sql);
      $rows = $query->result();

      $data=array();
      if ($query->num_rows()>0) {
        foreach($rows as $row) {
          array_push($data, array(
            'Descricao'=>iconv("Windows-1252", "utf-8//IGNORE", $row->DESCRICAO), 
            'Id'=>$row->ID_CPG, 
            'Inativo'=>$row->INATIVO, 
            'Financeiro'=>$row->FINANC,
            'MaxDiasCondPagam'=>$row->MAX_DIAS,
            'AllowSell'=>$row->ALLOW_SELL_FV,
            'CondicaoEspecial'=>$row->ESPECIAL,
            'PedidoMin'=>$row->PEDIDO_MIN,
            'IndPagam'=>$row->IND_PAGAM,
            'TipoOper'=>$row->OPER_TYPE));
        }
      }
      return $data;
    }

  }
