<?php

  class Produto_model extends CI_Model
  {    
        
    function __construct() {
        parent::__construct();
    }

    function getProduto($represId, $sincron) {

      $sql = "SELECT * FROM SEL_PRODUTO(" . $represId . "," . (int)$sincron . ")";

      $query = $this->db->query($sql);
      $rows = $query->result();

      $data=array();
      if ($query->num_rows()>0) {
        foreach($rows as $row) {
          array_push($data, array(
            'Id'=>$row->ID_PRO, 
            'Descricao'=>iconv("Windows-1252", "utf-8//IGNORE", $row->DESCRICAO), 
            'Inativo'=>$row->INATIVO,     
            'SubBrandId'=>$row->ID_SBR,
            'LinhaProdutoId'=>$row->ID_LIN,
            'FornecId'=>$row->ID_FOR,
            'PrecoUnit'=>$row->PRECO_UNIT,
            'PrecoUnit2'=>$row->PRECO_UNIT2,
            'PrecoUnitMin'=>$row->PRECO_UNIT_MIN,
            'PrecoCusto'=>$row->PRECO_CUSTO,
            'Desconto'=>$row->DESCONTO,
            'PesoBruto'=>$row->PESO_BRT,
            'ValidadeDesconto'=>$row->VALID_DCT,
            'AplicaFinanc'=>$row->APL_FINANC,
            'AplicaClassif'=>$row->APL_CLASSIF,
            'StatusEstoque'=>$row->STATUS_EST,
            'StatusDispon'=>$row->STATUS_DISP,
            'PrecoUnitMKT'=>$row->PRECO_MIN_MKT,
            'UseRepositorioWebImage'=>$row->USE_WEB_IMAGE,
            'BarCode'=>$row->BAR_CODE,
            'Detach'=>$row->DETACH,
            'TipoOper'=>$row->OPER_TYPE));
        }
      }
      return $data;
    }

  }
