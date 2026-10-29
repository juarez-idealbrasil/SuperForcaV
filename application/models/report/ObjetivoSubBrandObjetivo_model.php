<?php

  class ObjetivoSubBrandObjetivo_model extends CI_Model {

    private $mBaseReport;
            
    function __construct() {
        parent::__construct();
        $this->load->model('Report/BaseReport_model');
        $this->mBaseReport = new BaseReport_model();
    }

    private function setCommandSQL( $idRepres, $idEquipe, $reportMode ) {
      
      $mode = strcasecmp( $reportMode, 'valor' ) == 0 ? " SUM(OBJ_SBR_REP.VALOR) OBJETIVO " : " SUM(OBJ_SBR_REP.PESO) OBJETIVO ";

      $sql  = ' SELECT OBJ_SBR_REP.ID_SBR, ' ;
      $sql .= '        SUB_BRAND.DESCRICAO, ';
      $sql .= $mode;
      $sql .= ' FROM OBJ_SBR_REP ';
      $sql .= ' JOIN SUB_BRAND ON (OBJ_SBR_REP.ID_SBR = SUB_BRAND.ID_SBR) ';
      $sql .= ' JOIN REPRES    ON (REPRES.ID_PES      = OBJ_SBR_REP.ID_REP) ';

      if ( $idRepres > 0 ) {
        $sql .= ' AND REPRES.ID_PES = ' . $idRepres ;
      } else if ( $idEquipe > 0 ) {
        $sql .= ' AND REPRES.ID_EQU = ' . $idEquipe ;
      }

      $sql .= ' GROUP BY OBJ_SBR_REP.ID_SBR, ';
      $sql .= '          SUB_BRAND.DESCRICAO ';

      $sql .= ' ORDER BY SUB_BRAND.DESCRICAO ';

      return $sql;

    }

    function get( $idRepres, $idEquipe, $reportMode ) {

      $sql = $this->setCommandSQL( $idRepres, $idEquipe, $reportMode );

      $query = $this->db->query( $sql );
      $rows  = $query->result();

      $data = array();
      if ( $query->num_rows() > 0 ) {
        foreach( $rows as $row ) {
          array_push( $data, array(
            'idSBrand'  => $row->ID_SBR,
            'Descricao' => iconv("Windows-1252", "utf-8//IGNORE", $row->DESCRICAO), 
            'Objetivo'  => (float)$row->OBJETIVO,
            'Realizado' => (float)0,
            'Saldo'     => (float)0,
            'Positivado'=> (float)0
            )
        );
        }
      }
      return $data;
    }

    private function setCommandSQLSum( $idRepres, $idEquipe, $reportMode ) {
      
      $mode = $reportMode == 0 ? " SUM(OBJ_SBR_REP.VALOR) OBJETIVO " : " SUM(OBJ_SBR_REP.PESO) OBJETIVO ";

      $sql  = ' SELECT ' ;
      $sql .= $mode;
      $sql .= ' FROM OBJ_SBR_REP ';
      $sql .= ' JOIN SUB_BRAND ON (OBJ_SBR_REP.ID_SBR = SUB_BRAND.ID_SBR AND SUB_BRAND.INATIVO = 0) ';
      $sql .= ' JOIN REPRES    ON (OBJ_SBR_REP.ID_REP = REPRES.ID_PES) ';

      if ( $idRepres > 0 ) {
        $sql .= ' AND REPRES.ID_PES = ' . $idRepres ;
      } elseif ( $idEquipe > 0 ) {
        $sql .= ' AND REPRES.ID_EQU = ' . $idEquipe ;
      }

      return $sql;

    }

    function getSum( $idRepres, $idEquipe, $reportMode ) {

      $sql = $this->setCommandSQLSum( $idRepres, $idEquipe, $reportMode );

      $query = $this->db->query( $sql );
      $rows  = $query->result();

      $data = array();
      if ( $query->num_rows() > 0 ) {
        foreach( $rows as $row ) {
          array_push( $data, array(
            'Objetivo' => (float)$row->OBJETIVO
            )
        );
        }
      }

      return $data;
      
    }   


  }