<?php

  class OutrosMKTControl_model extends CI_Model {

    private $mBaseReport;
            
    function __construct() {
        parent::__construct();
        $this->load->model('Report/BaseReport_model');
        $this->mBaseReport = new BaseReport_model();		
    }

    private function setCommandSQL( $idRepres, $idEquipe, $dataIni, $dataFim ) {
      $dataIni = date( 'Y-m-d', strtotime( $dataIni ) );
      $dataFim = date( 'Y-m-d', strtotime( $dataFim ) );

      $sql  = " SELECT MKT.ID_REP, ";
      $sql .= " PESSOA.RAZ_SOC, ";
      $sql .= " COALESCE(SUM(MKT.VALOR_MKT) + SUM(MKT.CREDITO),0) SLD_GERAL ";
      $sql .= " FROM MKT ";
      $sql .= " JOIN REPRES ON (MKT.ID_REP = REPRES.ID_PES) ";
      $sql .= " JOIN PESSOA ON (PESSOA.ID_PES = REPRES.ID_PES) ";
      $sql .= " AND MKT.DATA <= CURRENT_DATE ";

      if ( $idRepres > 0 ) {
        $sql .= " AND REPRES.ID_PES = " . $idRepres ;
      } else if ( $idEquipe > 0 ) {
        $sql .= " AND REPRES.ID_EQU = " . $idEquipe ;
      }

      $sql .= " GROUP BY MKT.ID_REP, PESSOA.RAZ_SOC ";
      $sql .= " ORDER BY PESSOA.RAZ_SOC";

      return $sql;

    }

    function get( $idRepres, $idEquipe, $dataIni, $dataFim ) {

      $sql = $this->setCommandSQL( $idRepres, $idEquipe, $dataIni, $dataFim );

      $query = $this->db->query( $sql );
      $rows  = $query->result();

      $data = array();
      if ( $query->num_rows() > 0 ) {
        foreach( $rows as $row ) {
          array_push( $data, array(
            'idRepres'  => $row->ID_REP, 
            'RazSocial' => iconv("Windows-1252", "utf-8//IGNORE", $row->RAZ_SOC), 
            'Saldo'     => $row->SLD_GERAL 
            )
          );
        }
      }
      return $data;
    }

  }
