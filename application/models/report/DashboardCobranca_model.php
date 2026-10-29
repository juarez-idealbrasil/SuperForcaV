<?php

  class DashboardCobranca_model extends CI_Model {

    private $mBaseReport;
            
    function __construct() {
        parent::__construct();
        $this->load->model('Report/BaseReport_model');
        $this->mBaseReport = new BaseReport_model();
    }

    private function setCommandSQL( $idRepres, $idEquipe ) {

      $sql  = " SELECT ";
      $sql .= " SUM(IIF( COBRANCA.VENCTO < CURRENT_DATE, COBRANCA.TOTAL, 0)) VENCIDA, ";
      $sql .= " SUM(IIF( COBRANCA.VENCTO >= CURRENT_DATE, COBRANCA.TOTAL, 0)) VENCER ";
      $sql .= " FROM COBRANCA  ";
      $sql .= " JOIN REPRES ON (REPRES.ID_PES = COBRANCA.ID_REP) ";
      $sql .= " AND COBRANCA.BAIXA IS NULL ";

      if ( $idRepres > 0 ) {
        $sql .= " AND REPRES.ID_PES = " . $idRepres ;
      } else if ( $idEquipe > 0 ) {
        $sql .= " AND REPRES.ID_EQU = " . $idEquipe ;
      }

      return $sql;

    }

    function get( $idRepres, $idEquipe ) {

      $sql = $this->setCommandSQL( $idRepres, $idEquipe );

      $query = $this->db->query( $sql );
      $rows  = $query->result();

      $data = array();
      if ( $query->num_rows() > 0 ) {
        foreach( $rows as $row ) {
          array_push( $data, array(
            'Vencida' => $row->VENCIDA, 
            'Vencer'  => $row->VENCER
            )
          );
        }
      }
      return $data;
    }

  }
