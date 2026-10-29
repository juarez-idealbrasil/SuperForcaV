<?php

  class SubBrand_model extends CI_Model
  {    
        
    function __construct() {
        parent::__construct();
        $this->load->helper('accents_helper');
    }

    function getSubBrand($represId) {

      $sql = "SELECT * FROM SEL_SUB_BRAND(" . $represId . ")";
      
      $query = $this->db->query($sql);
      $rows = $query->result();

      $data=array();
      if ($query->num_rows() > 0) {
        foreach($rows as $row) {
            array_push($data, array(
              'Descricao'=>iconv("Windows-1252", "utf-8//IGNORE", $row->DESCRICAO),
              'Id'=>$row->ID_SBR,
              'Inativo'=>$row->INATIVO,
              'TipoOper'=>$row->OPER_TYPE)
            );
        }
      }
      return $data;
    }

  }
