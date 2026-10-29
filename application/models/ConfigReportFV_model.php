<?php

  class ConfigReportFV_model extends CI_Model
  {    
        
    function __construct() {
        parent::__construct();
    }

    function getConfigReportFV( $reportName ) {

      $sql  = " SELECT CFG_REL_FV.PERIODO, CFG_REL_FV.MODO, CFG_REL_FV.BLOQUEIO FROM CFG_REL_FV ";
      $sql .= " WHERE REL_NAME = '$reportName' ";

      $query = $this->db->query($sql);
      $row = $query->row();

      $data = null;
      if ( isset( $row ) ) {	  

        $dataIni = date( "Y/m/01", strtotime( "-".$row->PERIODO." month" ) );
        $dataFim = date( "Y/m/d" );		

        $data = array(
            'periodo'    =>$row->PERIODO,
            'report_mode'=>(int)$row->MODO,
            'bloqueio'   =>$row->BLOQUEIO,
            'data_ini'   =>$dataIni,
            'data_fim'   =>$dataFim,
        );        

      }

      return $data;      

    }

  }
