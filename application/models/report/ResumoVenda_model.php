<?php

  class ResumoVenda_model extends CI_Model {

    private $mBaseReport;
            
    function __construct() {
        parent::__construct();
        $this->load->model('Report/BaseReport_model');
        $this->mBaseReport = new BaseReport_model();
    }

    private function setCommandSQL( $idRepres, $idEquipe, $dataIni, $dataFim ) {
      $dataIni = date( 'Y-m-d', strtotime( $dataIni ) );
      $dataFim = date( 'Y-m-d', strtotime( $dataFim ) );

      $sql  = " SELECT PESSOA.RAZ_SOC, ";
      $sql .= " PEDIDO.EMISSAO,";
      $sql .= " PEDIDO.FATURA,";
      $sql .= " PEDIDO.TOTAL_NEG VALOR,";
      $sql .= " PEDIDO.PESO_BRT PESO,";
      $sql .= " COND_PAG.DESCRICAO COND_PG,";
      $sql .= " FORMA_PG.DESCRICAO FORMA_PG";
      $sql .= " FROM VENDA";
      $sql .= " JOIN PEDIDO   ON PEDIDO.ID_PED  = VENDA.ID_PED";
      $sql .= " JOIN PESSOA   ON (PESSOA.ID_PES = PEDIDO.ID_PES)";
      $sql .=   sprintf( " AND %s ", $this->mBaseReport->get()['SQLReportByFatur'] ) ;
      $sql .= " BETWEEN '" . $dataIni . "' AND '" . $dataFim . "'";
      $sql .= " AND PEDIDO.STATUS BETWEEN 1 AND 2";
      $sql .= " JOIN COND_PAG ON (VENDA.ID_CPG  = COND_PAG.ID_CPG)";
      $sql .= " JOIN FORMA_PG ON (VENDA.ID_FPG  = FORMA_PG.ID_FPG)";
      $sql .= " JOIN CLIENTE  ON (PEDIDO.ID_PES = CLIENTE.ID_PES)";
      $sql .=   sprintf( " JOIN REPRES   ON (REPRES.ID_PES = %s) ", $this->mBaseReport->get()['SQLRepresFromCliente'] ) ;

      if ( $idRepres > 0 ) {
        $sql .= " AND REPRES.ID_PES = " . $idRepres ;
      } else if ( $idEquipe > 0 ) {
        $sql .= " AND REPRES.ID_EQU = " . $idEquipe ;
      }
      $sql .= " ORDER BY PESSOA.RAZ_SOC,";
      $sql .=   sprintf( $this->mBaseReport->get()['SQLReportByFatur'] );

      return $sql;

    }

    function get( $idRepres, $idEquipe, $dataIni, $dataFim ) {

      $sql = $this->setCommandSQL( $idRepres, $idEquipe, $dataIni, $dataFim );

      $query = $this->db->query( $sql );
      $rows  = $query->result();

      $data['body']  = array();
      $data['total'] = array();
      $peso  = 0;
      $valor = 0;
      if ( $query->num_rows() > 0 ) {
        foreach( $rows as $row ) {
          $peso  += $row->PESO;
          $valor += $row->VALOR;          
          array_push( $data['body'], array(
            'RazSocial' => iconv("Windows-1252", "utf-8//IGNORE", $row->RAZ_SOC), 
            'Emissao'   => $row->EMISSAO, 
            'Fatura'    => $row->FATURA, 
            'Valor'     => $row->VALOR, 
            'Peso'      => $row->PESO,
            'CondPagam' => iconv("Windows-1252", "utf-8//IGNORE", $row->COND_PG), 
            'FormaPagam'=> iconv("Windows-1252", "utf-8//IGNORE", $row->FORMA_PG)
            )
          );
        }
      }
      $data['total']['Peso']  = $peso;
      $data['total']['Valor'] = $valor;
      return $data;
    }

  }
