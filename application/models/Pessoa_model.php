<?php

header('Content-Type: text/html; Charset=WIN1252');


  class Pessoa_model extends CI_Model
  {
            
    function __construct() {
        parent::__construct();
    }

    function getPessoa($represId) {

      // Manter segundo parametro para compatibilidade com a versão ForcaV5.1.14      
      
      $sql = "SELECT * FROM SEL_PESSOA(" . $represId . ", 0)";
      
      $query = $this->db->query($sql);
      $rows = $query->result();        

      $data=array();
      if ($query->num_rows()>0) {
        foreach($rows as $row) {
            array_push($data, array(
              'RazSocial'=>iconv("Windows-1252", "utf-8//IGNORE", $row->RAZ_SOC), 
              'Fantasia'=>iconv("Windows-1252", "utf-8//IGNORE", $row->FANTASIA) ,
              'TipoPessoa'=>$row->TIPO_PESSOA, 
              'Id'=>$row->ID_PES, 
              'EMail'=>iconv("Windows-1252", "utf-8//IGNORE", $row->E_MAIL) ,
              'RegiaoEntrega'=>iconv("Windows-1252", "utf-8//IGNORE", $row->REG_ENTR) ,
              'Inativo'=>$row->INATIVO, 
              'Endereco'=>iconv("Windows-1252", "utf-8//IGNORE", $row->ENDERECO) ,
			  'LastOrderDays'=>$row->LAST_ORDER_DAYS,
              'TipoOper'=>$row->OPER_TYPE));
        }
      }
      
      return $data;
    }

  }
