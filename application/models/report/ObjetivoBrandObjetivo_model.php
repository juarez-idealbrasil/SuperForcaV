<?php

  class ObjetivoBrandObjetivo_model extends CI_Model {

    private $mBaseReport;
            
    function __construct() {
        parent::__construct();
        $this->load->model('Report/BaseReport_model');
        $this->mBaseReport = new BaseReport_model();
    }

    private function setCommandSQL( $idRepres, $idEquipe, $reportMode ) {

      $mode = strcasecmp( $reportMode, 'valor' ) == 0 ? " SUM(OBJ_BRA_REP.VALOR) OBJETIVO " : " SUM(OBJ_BRA_REP.PESO) OBJETIVO ";      

      $sql  = ' SELECT OBJ_BRA_REP.ID_BRA, ' ;
      $sql .= '        BRAND.DESCRICAO, ';
      $sql .= $mode;
      $sql .= ' FROM OBJ_BRA_REP ';
      $sql .= ' JOIN BRAND  ON (OBJ_BRA_REP.ID_BRA = BRAND.ID_BRA) ';
      $sql .= ' JOIN REPRES ON (REPRES.ID_PES      = OBJ_BRA_REP.ID_REP) ';

      if ( $idRepres > 0 ) {
        $sql .= ' AND REPRES.ID_PES = ' . $idRepres ;
      } else if ( $idEquipe > 0 ) {
        $sql .= ' AND REPRES.ID_EQU = ' . $idEquipe ;
      }

      $sql .= ' GROUP BY OBJ_BRA_REP.ID_BRA, ';
      $sql .= '          BRAND.DESCRICAO ';

      $sql .= ' ORDER BY BRAND.DESCRICAO ';

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
            'idBrand'   => $row->ID_BRA,
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
      
      $mode = strcasecmp( $reportMode, 'valor' ) == 0 ? " SUM(OBJ_BRA_REP.VALOR) OBJETIVO " : " SUM(OBJ_BRA_REP.PESO) OBJETIVO ";

      $sql  = ' SELECT ' ;
      $sql .= $mode;
      $sql .= ' FROM OBJ_BRA_REP ';
      $sql .= ' JOIN BRAND  ON (OBJ_BRA_REP.ID_BRA = BRAND.ID_BRA AND BRAND.INATIVO = 0) ';
      $sql .= ' JOIN REPRES ON (OBJ_BRA_REP.ID_REP = REPRES.ID_REP) ';

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