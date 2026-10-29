<?php

  class Fornecedor_model extends CI_Model
  {
            
    function __construct() {
        parent::__construct();
    }

    function getFornecedor($represId) {

      $sql = "SELECT * FROM SEL_FORNECEDOR(" . $represId . ")";

      $query = $this->db->query($sql);
      $rows = $query->result();

      $data=array();
      if ($query->num_rows()>0) {
        foreach($rows as $row) {
          array_push($data, array(
            'RazSocial'=>iconv("Windows-1252", "utf-8//IGNORE", $row->RAZ_SOC), 
            'Fantasia'=>iconv("Windows-1252", "utf-8//IGNORE", $row->FANTASIA), 
            'TipoPessoa'=>$row->TIPO_PESSOA, 
            'Id'=>$row->ID_PES, 
            'EMail'=>$row->E_MAIL,
            'Inativo'=>$row->INATIVO,
			'LastOrderDays'=>$row->LAST_ORDER_DAYS,
            'TipoOper'=>$row->OPER_TYPE));
        }
      }
      return $data;
    }

  }
