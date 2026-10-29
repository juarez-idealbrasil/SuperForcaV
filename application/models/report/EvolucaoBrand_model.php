<?php

  class EvolucaoBrand_model extends CI_Model {

    private $mBaseReport;
            
    function __construct() {
        parent::__construct();
        $this->load->model('Report/BaseReport_model');
        $this->mBaseReport = new BaseReport_model();
    }

    private function setCommandSQL( $idRepres, $idEquipe, $dataIni, $dataFim, $reportMode ) {
      
      $dataIni = date( 'Y-m-d', strtotime( $dataIni ) );
      $dataFim = date( 'Y-m-d', strtotime( $dataFim ) );
      $mode    = $reportMode == 0 ? " ITEM_PED.VALOR_ITEM " : " ITEM_PED.QUANT * PRODUTO.PESO_BRT ";

      $sql  = ' SELECT BRAND.ID_BRA, ' ;
      $sql .= '        BRAND.ABREVIADO, ';
      $sql .= '        SUM(IIF ( EXTRACT(MONTH FROM ' . $this->mBaseReport->get()['SQLReportByFatur'] . ') = 1,' . $mode .', 0 )) "JAN", ';
      $sql .= '        SUM(IIF ( EXTRACT(MONTH FROM ' . $this->mBaseReport->get()['SQLReportByFatur'] . ') = 2,' . $mode .', 0 )) "FEV", ';
      $sql .= '        SUM(IIF ( EXTRACT(MONTH FROM ' . $this->mBaseReport->get()['SQLReportByFatur'] . ') = 3,' . $mode .', 0 )) "MAR", ';
      $sql .= '        SUM(IIF ( EXTRACT(MONTH FROM ' . $this->mBaseReport->get()['SQLReportByFatur'] . ') = 4,' . $mode .', 0 )) "ABR", ';
      $sql .= '        SUM(IIF ( EXTRACT(MONTH FROM ' . $this->mBaseReport->get()['SQLReportByFatur'] . ') = 5,' . $mode .', 0 )) "MAI", ';
      $sql .= '        SUM(IIF ( EXTRACT(MONTH FROM ' . $this->mBaseReport->get()['SQLReportByFatur'] . ') = 6,' . $mode .', 0 )) "JUN", ';
      $sql .= '        SUM(IIF ( EXTRACT(MONTH FROM ' . $this->mBaseReport->get()['SQLReportByFatur'] . ') = 7,' . $mode .', 0 )) "JUL", ';
      $sql .= '        SUM(IIF ( EXTRACT(MONTH FROM ' . $this->mBaseReport->get()['SQLReportByFatur'] . ') = 8,' . $mode .', 0 )) "AGO", ';
      $sql .= '        SUM(IIF ( EXTRACT(MONTH FROM ' . $this->mBaseReport->get()['SQLReportByFatur'] . ') = 9,' . $mode .', 0 )) "SET", ';
      $sql .= '        SUM(IIF ( EXTRACT(MONTH FROM ' . $this->mBaseReport->get()['SQLReportByFatur'] . ') =10,' . $mode .', 0 )) "OUT", ';
      $sql .= '        SUM(IIF ( EXTRACT(MONTH FROM ' . $this->mBaseReport->get()['SQLReportByFatur'] . ') =11,' . $mode .', 0 )) "NOV", ';
      $sql .= '        SUM(IIF ( EXTRACT(MONTH FROM ' . $this->mBaseReport->get()['SQLReportByFatur'] . ') =12,' . $mode .', 0 )) "DEZ" ' ;
      $sql .= ' FROM PEDIDO ';
      $sql .= ' JOIN ITEM_PED  ON (PEDIDO.ID_PED  = ITEM_PED.ID_PED) ';
      $sql .=   sprintf( ' AND %s ', $this->mBaseReport->get()['SQLReportByFatur'] ) ;
      $sql .= " BETWEEN '" . $dataIni . "' AND '" . $dataFim . "'";
      $sql .= ' AND PEDIDO.STATUS BETWEEN 1 AND 2 ';
      $sql .= ' JOIN CFOP      ON (CFOP.ID_CFOP   = ITEM_PED.ID_CFOP) ';
      $sql .=   sprintf( ' AND %s ', $this->mBaseReport->get()['SQLAddBonificReport'] ) ;
      $sql .= ' JOIN CLIENTE   ON (PEDIDO.ID_PES    = CLIENTE.ID_PES) ';
      $sql .= ' JOIN PRODUTO   ON (ITEM_PED.ID_PRO  = PRODUTO.ID_PRO) ';
      $sql .= ' JOIN SUB_BRAND ON (SUB_BRAND.ID_SBR = PRODUTO.ID_SBR) ';
      $sql .= ' JOIN BRAND     ON (SUB_BRAND.ID_BRA = BRAND.ID_BRA) ';
      $sql .=   sprintf( ' JOIN REPRES   ON (REPRES.ID_PES = %s) ', $this->mBaseReport->get()['SQLRepresFromCliente'] ) ;
      $sql .= ' AND STRLEN(TRIM(BRAND.ABREVIADO)) > 0 ';

      if ( $idRepres > 0 ) {
        $sql .= " AND REPRES.ID_PES = " . $idRepres ;
      } else if ( $idEquipe > 0 ) {
        $sql .= " AND REPRES.ID_EQU = " . $idEquipe ;
      }

      $sql .= " GROUP BY BRAND.ID_BRA, ";
      $sql .= "          BRAND.ABREVIADO ";     

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
            'idBrand'   => $row->ID_BRA,
            'Descricao' => iconv("Windows-1252", "utf-8//IGNORE", $row->ABREVIADO), 
            'Janeiro'   => $row->JAN, 
            'Fevereiro' => $row->FEV,
            'Marco'     => $row->MAR,
            'Abril'     => $row->ABR,
            'Maio'      => $row->MAI,
            'Junho'     => $row->JUN,
            'Julho'     => $row->JUL,
            'Agosto'    => $row->AGO,
            'Setembro'  => $row->SET,
            'Outubro'   => $row->OUT,
            'Novembro'  => $row->NOV,
            'Dezembro'  => $row->DEZ,
            )
        );
        }
      }
      return $data;
    }   

  }