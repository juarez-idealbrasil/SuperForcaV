<?php

  class ObjetivoSubBrandRealizado_model extends CI_Model {

    private $mBaseReport;
            
    function __construct() {
        parent::__construct();
        $this->load->model('Report/BaseReport_model');
        $this->mBaseReport = new BaseReport_model();
    }

    private function setCommandSQL( $idRepres, $idEquipe, $dataIni, $dataFim, $reportMode ) {
      
      $dataIni = date( 'Y-m-d', strtotime( $dataIni ) );
      $dataFim = date( 'Y-m-d', strtotime( $dataFim ) );
      $mode    = strcasecmp( $reportMode, 'valor' ) == 0 ? ' SUM(ITEM_PED.VALOR_ITEM) REALIZADO ' : ' SUM(ITEM_PED.QUANT * PRODUTO.PESO_BRT) REALIZADO ';

      $sql  = ' SELECT SUB_BRAND.ID_SBR, ' ;
      $sql .= $mode;
      $sql .= ' FROM VENDA ';
      $sql .= ' JOIN PEDIDO   ON (PEDIDO.ID_PED   = VENDA.ID_PED) ';
      $sql .= ' JOIN ITEM_PED ON (ITEM_PED.ID_PED = PEDIDO.ID_PED) ';
      $sql .=   sprintf( ' AND %s ', $this->mBaseReport->get()['SQLReportByFatur'] ) ;
      $sql .= " BETWEEN '" . $dataIni . "' AND '" . $dataFim . "'";
      $sql .= ' AND PEDIDO.STATUS BETWEEN 1 AND 2 ';
      $sql .= ' JOIN CFOP      ON (CFOP.ID_CFOP   = ITEM_PED.ID_CFOP) ';
      $sql .= ' AND CFOP.CLASSIF = 1 ';      
      $sql .= ' JOIN PRODUTO   ON (ITEM_PED.ID_PRO  = PRODUTO.ID_PRO) ';
      $sql .= ' JOIN SUB_BRAND ON (SUB_BRAND.ID_SBR = PRODUTO.ID_SBR) ';
      $sql .= ' JOIN CLIENTE   ON (PEDIDO.ID_PES    = CLIENTE.ID_PES) ';     
      $sql .=   sprintf( ' JOIN REPRES   ON (REPRES.ID_PES = %s) ', $this->mBaseReport->get()['SQLRepresFromCliente'] ) ;

      if ( $idRepres > 0 ) {
        $sql .= ' AND REPRES.ID_PES = ' . $idRepres ;
      } else if ( $idEquipe > 0 ) {
        $sql .= ' AND REPRES.ID_EQU = ' . $idEquipe ;
      }

      $sql .= ' GROUP BY SUB_BRAND.ID_SBR ';

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
            'idSBrand'  => $row->ID_SBR,
            'Realizado' => $row->REALIZADO 
            )
        );
        }
      }
      return $data;
    }

    private function setCommandSQLSum( $idRepres, $idEquipe, $dataIni, $dataFim, $reportMode ) {
      
      $dataIni = date( 'Y-m-d', strtotime( $dataIni ) );
      $dataFim = date( 'Y-m-d', strtotime( $dataFim ) );
      $mode    = $reportMode == 0 ? ' SUM(ITEM_PED.VALOR_ITEM) REALIZADO ' : ' SUM(ITEM_PED.QUANT * PRODUTO.PESO_BRT) REALIZADO ';

      $sql  = ' SELECT ' ;
      $sql .= $mode;
      $sql .= ' FROM VENDA ';
      $sql .= ' JOIN PEDIDO      ON (PEDIDO.ID_PED   = VENDA.ID_PED) ';
      $sql .= ' JOIN ITEM_PED    ON (ITEM_PED.ID_PED = PEDIDO.ID_PED) ';
      $sql .=   sprintf( ' AND %s ', $this->mBaseReport->get()['SQLReportByFatur'] ) ;
      $sql .= " BETWEEN '" . $dataIni . "' AND '" . $dataFim . "'";
      $sql .= ' AND PEDIDO.STATUS BETWEEN 1 AND 2 ';
      $sql .= ' JOIN CFOP        ON (CFOP.ID_CFOP   = ITEM_PED.ID_CFOP) ';
      $sql .= ' AND CFOP.CLASSIF = 1 ';      
      $sql .= ' JOIN PRODUTO     ON (ITEM_PED.ID_PRO  = PRODUTO.ID_PRO) ';
      $sql .= ' JOIN SUB_BRAND   ON (SUB_BRAND.ID_SBR = PRODUTO.ID_SBR) ';
      $sql .= ' JOIN CLIENTE     ON (PEDIDO.ID_PES    = CLIENTE.ID_PES) ';     
      $sql .=   sprintf( ' JOIN REPRES   ON (REPRES.ID_PES = %s) ', $this->mBaseReport->get()['SQLRepresFromCliente'] ) ;
      $sql .= ' JOIN OBJ_SBR_REP ON (SUB_BRAND.ID_SBR = OBJ_SBR_REP.ID_SBR) ';

      if ( $idRepres > 0 ) {
        $sql .= ' AND REPRES.ID_PES = ' . $idRepres . ' AND OBJ_SBR_REP.ID_REP = ' . $idRepres ;
      } else if ( $idEquipe > 0 ) {
        $sql .= ' AND REPRES.ID_EQU = ' . $idEquipe ;
      }

      return $sql;

    }

    function getSum( $idRepres, $idEquipe, $dataIni, $dataFim, $reportMode ) {

      $sql = $this->setCommandSQLSum( $idRepres, $idEquipe, $dataIni, $dataFim, $reportMode );

      $query = $this->db->query( $sql );
      $rows  = $query->result();

      $data = array();
      if ( $query->num_rows() > 0 ) {
        foreach( $rows as $row ) {
          array_push( $data, array(
            'Realizado' => $row->REALIZADO 
            )
        );
        }
      }
      return $data;
    }   


  }