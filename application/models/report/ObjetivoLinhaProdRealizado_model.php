<?php

  class ObjetivoLinhaProdRealizado_model extends CI_Model {

    private $mBaseReport;
            
    function __construct() {
        parent::__construct();
        $this->load->model('Report/BaseReport_model');
        $this->mBaseReport = new BaseReport_model();
    }

    private function setCommandSQL( $idRepres, $idEquipe, $dataIni, $dataFim, $reportMode ) {
      
      $dataIni = date( 'Y-m-d', strtotime( $dataIni ) );
      $dataFim = date( 'Y-m-d', strtotime( $dataFim ) );
      $mode    = $reportMode == 0 ? " SUM(CASE CFOP.CLASSIF WHEN 1 THEN ITEM_PED.VALOR_ITEM ELSE 0 END) REALIZADO " : " SUM(ITEM_PED.QUANT * PRODUTO.PESO_BRT) REALIZADO ";

      $sql  = ' SELECT LINHA_PROD.ID_LIN, ' ;
      $sql .= $mode;
      $sql .= ' FROM PEDIDO ';
      $sql .= ' JOIN ITEM_PED ON (ITEM_PED.ID_PED = PEDIDO.ID_PED) ';
      $sql .=   sprintf( ' AND %s ', $this->mBaseReport->get()['SQLReportByFatur'] ) ;
      $sql .= " BETWEEN '" . $dataIni . "' AND '" . $dataFim . "'";
      $sql .= ' AND PEDIDO.STATUS BETWEEN 1 AND 2 ';
      $sql .= ' JOIN CFOP      ON (CFOP.ID_CFOP   = ITEM_PED.ID_CFOP) ';
      $sql .=   sprintf( ' AND %s ', $this->mBaseReport->get()['SQLAddBonificReport'] ) ;
      $sql .= ' JOIN PRODUTO    ON (ITEM_PED.ID_PRO   = PRODUTO.ID_PRO) ';
      $sql .= ' JOIN SUB_BRAND  ON (SUB_BRAND.ID_SBR  = PRODUTO.ID_SBR) ';
      $sql .= ' JOIN LINHA_PROD ON (LINHA_PROD.ID_LIN = PRODUTO.ID_LIN) ';
      $sql .= ' JOIN CLIENTE    ON (PEDIDO.ID_PES     = CLIENTE.ID_PES) ';     
      $sql .=   sprintf( ' JOIN REPRES   ON (REPRES.ID_PES = %s) ', $this->mBaseReport->get()['SQLRepresFromCliente'] ) ;

      if ( $idRepres > 0 ) {
        $sql .= ' AND REPRES.ID_PES = ' . $idRepres ;
      } else if ( $idEquipe > 0 ) {
        $sql .= ' AND REPRES.ID_EQU = ' . $idEquipe ;
      }

      $sql .= ' GROUP BY LINHA_PROD.ID_LIN ';

      return $sql;

    }

    function get( $idRepres, $idEquipe, $dataIni, $dataFim, $reportMode ) {

      $sql = $this->setCommandSQL( $idRepres, $idEquipe, $dataIni, $dataFim, $reportMode );

      $query = $this->db->query( $sql );
      $rows  = $query->result();

      $data = array();
      if ( $query->num_rows() > 0 ) {
        foreach( $rows as $row ) {
          array_push( $data, array(
            'idLinha'   => $row->ID_LIN,
            'Realizado' => $row->REALIZADO 
            )
        );
        }
      }
      return $data;
    }   

  }