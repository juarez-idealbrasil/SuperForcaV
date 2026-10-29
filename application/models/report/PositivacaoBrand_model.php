<?php

  class PositivacaoBrand_model extends CI_Model {

    private $mBaseReport;
            
    function __construct() {
        parent::__construct();
        $this->load->model('Report/BaseReport_model');
        $this->mBaseReport = new BaseReport_model();		
    }

    private function setCommandSQL( $idRepres, $idEquipe, $dataIni, $dataFim ) {
      $dataIni = date( 'Y-m-d', strtotime( $dataIni ) );
      $dataFim = date( 'Y-m-d', strtotime( $dataFim ) );

      $sql  = " SELECT BRAND.ID_BRA, ";
      $sql .= "        BRAND.ABREVIADO DESCRICAO,";
      $sql .= "        SUM(CASE CFOP.CLASSIF";
      $sql .= "        WHEN 1 THEN ITEM_PED.VALOR_ITEM";
      $sql .= "        ELSE 0";
      $sql .= "        END) VALOR,";
      $sql .= "        SUM(ITEM_PED.QUANT * PRODUTO.PESO_BRT) PESO,";
      $sql .= "        COUNT(DISTINCT(PEDIDO.ID_PES)) CA, ";
      $sql .= "       (SELECT COUNT(CLIENTE.ID_PES) FROM CLIENTE JOIN PESSOA ON (CLIENTE.ID_PES = PESSOA.ID_PES) AND PESSOA.INATIVO = 0 ";
      $sql .=          $idRepres > 0 ? " AND CLIENTE.ID_REP = '$idRepres' " : "";
      $sql .=         " ) CC, ";
      $sql .= "        SUM(ITEM_PED.QUANT) UNIDADE, ";
      $sql .= "        COUNT(DISTINCT(ITEM_PED.ID_PRO)) VARIEDADE ";
      $sql .= " FROM PEDIDO ";
      $sql .= " JOIN ITEM_PED  ON (PEDIDO.ID_PED = ITEM_PED.ID_PED) ";
      $sql .=   sprintf( " AND %s ", $this->mBaseReport->get()['SQLReportByFatur'] ) ;
      $sql .= " BETWEEN '" . $dataIni . "' AND '" . $dataFim . "'";
      $sql .= " AND PEDIDO.STATUS BETWEEN 1 AND 2 ";
      $sql .= " JOIN CFOP      ON (CFOP.ID_CFOP  = ITEM_PED.ID_CFOP) ";
      $sql .=   sprintf( " AND %s ", $this->mBaseReport->get()['SQLAddBonificReport'] ) ;
      $sql .= " JOIN CLIENTE  ON (PEDIDO.ID_PES = CLIENTE.ID_PES)";
      $sql .=   sprintf( " JOIN REPRES   ON (REPRES.ID_PES = %s) ", $this->mBaseReport->get()['SQLRepresFromCliente'] ) ;
      $sql .= " JOIN PESSOA    ON (PESSOA.ID_PES  = REPRES.ID_PES) ";
      $sql .= " JOIN PRODUTO   ON (PRODUTO.ID_PRO = ITEM_PED.ID_PRO) ";
      $sql .= " JOIN SUB_BRAND ON (PRODUTO.ID_SBR = SUB_BRAND.ID_SBR) ";
      $sql .= " JOIN BRAND     ON (SUB_BRAND.ID_BRA = BRAND.ID_BRA) ";

      if ( $idRepres > 0 ) {
        $sql .= " AND REPRES.ID_PES = " . $idRepres ;
      } else if ( $idEquipe > 0 ) {
        $sql .= " AND REPRES.ID_EQU = " . $idEquipe ;
      }

      $sql .= " AND STRLEN(BRAND.ABREVIADO) > 0 ";

      $sql .= " GROUP BY BRAND.ID_BRA, 2 ";
      
      $sql .= " ORDER BY BRAND.ABREVIADO ";	  

      return $sql;

    }

    function get( $idRepres, $idEquipe, $dataIni, $dataFim ) {

      $sql = $this->setCommandSQL( $idRepres, $idEquipe, $dataIni, $dataFim );

      $query = $this->db->query( $sql );
      $rows  = $query->result();

      $data['body']  = array();
      $data['total'] = array();
      $peso    = 0;
      $valor   = 0;
      $unidade = 0;
      if ( $query->num_rows() > 0 ) {
        foreach( $rows as $row ) {
          $peso    += $row->PESO;
          $valor   += $row->VALOR;
          $unidade += $row->UNIDADE; 
          $Positivacao = $row->CA > 0 && $row->CC > 0 ? ($row->CA / $row->CC) * 100 : 0.0;
          array_push( $data['body'], array(
            'Descricao'    => iconv("Windows-1252", "utf-8//IGNORE", $row->DESCRICAO), 
            'Valor'        => (float)$row->VALOR, 
            'Peso'         => (float)$row->PESO,
            'ClienteAtend' => $row->CA, 
            'ClienteCad'   => $row->CC,
            'Unidade'      => $row->UNIDADE,
            'Variedade'    => $row->VARIEDADE,
            'Positivacao'  => $Positivacao
            )
        );
        }
      }
      $data['total']['Peso']    = $peso;
      $data['total']['Valor']   = $valor;
      $data['total']['Unidade'] = $unidade;
      return $data;
    }

  }
