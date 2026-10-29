<?php

  class OutrosSBrandClienteRealizado_model extends CI_Model {

    private $mBaseReport;
            
    function __construct() {
        parent::__construct();
        $this->load->model('Report/BaseReport_model');
        $this->mBaseReport = new BaseReport_model();
    }

    private function setCommandSQL( $idRepres, $idEquipe, $dataIni, $dataFim, $reportMode ) {
      
      $dataIni = date( 'Y-m-d', strtotime( $dataIni ) );
      $dataFim = date( 'Y-m-d', strtotime( $dataFim ) );
      $mode    = $reportMode == 0 ? ' SUM(ITEM_PED.QUANT * PRODUTO.PESO_BRT) VALOR ' : ' SUM(CASE CFOP.CLASSIF WHEN 1 THEN ITEM_PED.VALOR_ITEM ELSE 0 END) VALOR ';

      $sql  = ' SELECT CLIENTE.ID_PES, ' ;
      $sql .= '        PESSOA.RAZ_SOC, ';
      $sql .= '        SUB_BRAND.ID_SBR, ';
      $sql .= $mode;
      $sql .= ' FROM PEDIDO ';
      $sql .= ' JOIN ITEM_PED  ON (ITEM_PED.ID_PED = PEDIDO.ID_PED) ';
      $sql .=   sprintf( ' AND %s ', $this->mBaseReport->get()['SQLReportByFatur'] ) ;
      $sql .= " BETWEEN '" . $dataIni . "' AND '" . $dataFim . "'";
      $sql .= ' AND PEDIDO.STATUS BETWEEN 1 AND 2 ';
      $sql .= ' JOIN CFOP      ON (CFOP.ID_CFOP   = ITEM_PED.ID_CFOP) ';
      $sql .=   sprintf( " AND %s ", $this->mBaseReport->get()['SQLAddBonificReport'] ) ;
      $sql .= ' JOIN CLIENTE   ON (PEDIDO.ID_PES    = CLIENTE.ID_PES) ';
      $sql .= ' JOIN PESSOA    ON (PESSOA.ID_PES    = CLIENTE.ID_PES) ';
      $sql .= ' JOIN PRODUTO   ON (ITEM_PED.ID_PRO  = PRODUTO.ID_PRO) ';
      $sql .= ' JOIN SUB_BRAND ON (SUB_BRAND.ID_SBR = PRODUTO.ID_SBR) ';
      $sql .=   sprintf( ' JOIN REPRES ON (REPRES.ID_PES = %s) ', $this->mBaseReport->get()['SQLRepresFromCliente'] ) ;

      if ( $idRepres > 0 ) {
        $sql .= ' AND REPRES.ID_PES = ' . $idRepres ;
      } elseif ( $idEquipe > 0 ) {
        $sql .= ' AND REPRES.ID_EQU = ' . $idEquipe ;
      }

      $sql .= ' AND STRLEN(SUB_BRAND.ABREVIADO) > 0 AND SUB_BRAND.INATIVO = 0 ';
      $sql .= ' GROUP BY CLIENTE.ID_PES, ';
      $sql .= '          PESSOA.RAZ_SOC, '; 
      $sql .= '          SUB_BRAND.ID_SBR ';

      $sql .= ' ORDER BY PESSOA.RAZ_SOC ';

      return $sql;

    }

    private function setCommandSQLCustomers( $idRepres, $idEquipe, $dataIni, $dataFim ) {
      
      $dataIni = date( 'Y-m-d', strtotime( $dataIni ) );
      $dataFim = date( 'Y-m-d', strtotime( $dataFim ) );

      $sql  = ' SELECT PESSOA.RAZ_SOC ';
      $sql .= ' FROM PEDIDO ';
      $sql .= ' JOIN ITEM_PED  ON (ITEM_PED.ID_PED = PEDIDO.ID_PED) ';
      $sql .=   sprintf( ' AND %s ', $this->mBaseReport->get()['SQLReportByFatur'] ) ;
      $sql .= " BETWEEN '" . $dataIni . "' AND '" . $dataFim . "'";
      $sql .= ' AND PEDIDO.STATUS BETWEEN 1 AND 2 ';
      $sql .= ' JOIN CFOP      ON (CFOP.ID_CFOP   = ITEM_PED.ID_CFOP) ';
      $sql .=   sprintf( " AND %s ", $this->mBaseReport->get()['SQLAddBonificReport'] ) ;
      $sql .= ' JOIN CLIENTE   ON (PEDIDO.ID_PES    = CLIENTE.ID_PES) ';
      $sql .= ' JOIN PESSOA    ON (PESSOA.ID_PES    = CLIENTE.ID_PES) ';
      $sql .= ' JOIN PRODUTO   ON (ITEM_PED.ID_PRO  = PRODUTO.ID_PRO) ';
      $sql .= ' JOIN SUB_BRAND ON (SUB_BRAND.ID_SBR = PRODUTO.ID_SBR) ';
      $sql .=   sprintf( ' JOIN REPRES ON (REPRES.ID_PES = %s) ', $this->mBaseReport->get()['SQLRepresFromCliente'] ) ;

      if ( $idRepres > 0 ) {
        $sql .= ' AND REPRES.ID_PES = ' . $idRepres ;
      } elseif ( $idEquipe > 0 ) {
        $sql .= ' AND REPRES.ID_EQU = ' . $idEquipe ;
      }

      $sql .= ' AND STRLEN(SUB_BRAND.ABREVIADO) > 0 AND SUB_BRAND.INATIVO = 0 ';
      $sql .= ' GROUP BY PESSOA.RAZ_SOC '; 

      $sql .= ' ORDER BY PESSOA.RAZ_SOC ';      

      return $sql;

    }

    function get( $idRepres, $idEquipe, $dataIni, $dataFim, $reportMode ) {

      $customers = $this->getCustomers($idRepres, $idEquipe, $dataIni, $dataFim);

      $sql = $this->setCommandSQL( $idRepres, $idEquipe, $dataIni, $dataFim, $reportMode );

      $query = $this->db->query( $sql );
      $rows  = $query->result();

      $data = array();
      if ( $query->num_rows() > 0 ) {
        foreach( $rows as $row ) {
          array_push( $data, array(
            'RazSocial' => iconv("Windows-1252", "utf-8//IGNORE", $row->RAZ_SOC),
            'idSBrand'  => $row->ID_SBR,
            'Valor'     => $row->VALOR
            ));        
        }
      }

      foreach( $customers as &$customer ) {

          foreach( $data as $item ) {

            if ( $item['RazSocial'] == $customer[0] ) {

              $customer[] = array('idSBrand' => $item['idSBrand'], 'Valor' => $item['Valor'] );

            }

          }

      }

      return $customers;

    }

    function getCustomers( $idRepres, $idEquipe, $dataIni, $dataFim ) {

      $sql = $this->setCommandSQLCustomers( $idRepres, $idEquipe, $dataIni, $dataFim );

      $query = $this->db->query( $sql );
      $rows  = $query->result();

      $customers = array();
      if ( $query->num_rows() > 0 ) {
        foreach( $rows as $row ) {
          array_push( $customers, array(
            iconv("Windows-1252", "utf-8//IGNORE", $row->RAZ_SOC)
            )
        );
        }
      }
      return $customers;
    }   

  }