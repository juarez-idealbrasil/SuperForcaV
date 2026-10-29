<?php

  class ObjetivoLinhaProdObjetivo_model extends CI_Model {

    private $mBaseReport;
            
    function __construct() {
        parent::__construct();
        $this->load->model('Report/BaseReport_model');
        $this->mBaseReport = new BaseReport_model();
    }

    private function setCommandSQL( $idRepres, $idEquipe, $reportMode ) {
      
      $mode = $reportMode == 0 ? " OBJ_SBR_REP.VALOR OBJETIVO " : " OBJ_SBR_REP.PESO OBJETIVO ";

      $sql  = ' SELECT DISTINCT LINHA_PROD.ID_LIN, ' ;
      $sql .= '                 LINHA_PROD.DESCRICAO, ';
      $sql .= $mode;
      $sql .= ' FROM LINHA_PROD ';
      $sql .= ' JOIN PRODUTO     ON (LINHA_PROD.ID_LIN  = PRODUTO.ID_LIN) ';
      $sql .= ' JOIN OBJ_SBR_REP ON (OBJ_SBR_REP.ID_SBR = PRODUTO.ID_SBR) ';
      $sql .= ' JOIN REPRES      ON (REPRES.ID_PES      = OBJ_SBR_REP.ID_REP) ';

      if ( $idRepres > 0 ) {
        $sql .= ' AND REPRES.ID_PES = ' . $idRepres ;
      } else if ( $idEquipe > 0 ) {
        $sql .= ' AND REPRES.ID_EQU = ' . $idEquipe ;
      }

      $sql .= ' ORDER BY LINHA_PROD.DESCRICAO ';

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
            'idLinha'   => $row->ID_LIN,
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

  }