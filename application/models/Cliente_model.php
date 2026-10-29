<?php

  class Cliente_model extends CI_Model
  {    
        
    function __construct() {
        parent::__construct();
    }

    function getCliente($represId, $sincron) {

      $sql = "SELECT * FROM SEL_CLIENTE(" . $represId . "," . (int)$sincron . ")";

      //return $sql;

      $query = $this->db->query($sql);
      $rows = $query->result();

      $data=array();
      if ($query->num_rows()>0) {
        foreach($rows as $row) {
          array_push($data, array(
              'ClassifClieId'=>$row->ID_CLA, 
              'Id'=>$row->ID_PES, 
              'DiaVisita'=>$row->DIA_VISITA, 
              'Desconto'=>$row->DESCONTO,
              'RestrFormaPagam'=>$row->RESTRIC_FPG,
              'MaxDiasCondPagam'=>$row->MAX_DIAS_CPG,
              'Segmento'=>$row->SEGMENTO,
              'SaldoDeposito'=>$row->SALDO_DEPOS,
              'CondicaoEspecial'=>$row->COND_ESPEC,
              'RestriCompra'=>$row->RESTRIC_COMP,
              'Fone'=>$row->FONE,
              'LimiteCredito'=>$row->LIM_CREDITO,
              'TabelaPrecoId'=>$row->ID_TPR,
              'TipoOper'=>$row->OPER_TYPE));
        }
      }
      return $data;
    }

  }
