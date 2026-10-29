<?php

  class Representante_model extends CI_Model
  {    
        
    function __construct() {
        parent::__construct();
    }

    function getRepresentante($represId) {

      $sql = "SELECT * FROM SEL_REPRESENTANTE(" . $represId . ")";

      $query = $this->db->query($sql);
      $rows = $query->result();

      $data=array();
      if ( $query->num_rows()>0 ) {
        foreach( $rows as $row ) {
          array_push($data, array( 'SaldoMKT'=>$row->SALDO_MKT, 'Id'=>$row->ID_PES, 'TipoOper'=>$row->OPER_TYPE ) );
        }
      }
      return $data;
    }

  }
