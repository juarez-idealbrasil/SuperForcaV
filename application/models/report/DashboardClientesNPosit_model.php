<?php

  class DashboardClientesNPosit_model extends CI_Model {

    private $mBaseReport;
            
    function __construct() {
        parent::__construct();
        $this->load->model('Report/BaseReport_model');
        $this->mBaseReport = new BaseReport_model();
    }

    private function setCommandSQL( $idRepres ) {

      $sql  = " WITH CLIENTES AS ( ";
      $sql .= " SELECT  PESSOA.ID_PES, ";
      $sql .= "         MAX(PESSOA.DT_ULT_PED) MAX_PED ";
      $sql .= " FROM PESSOA ";
      $sql .= " JOIN PERFIL_CLIENTE_REPRES ON (PERFIL_CLIENTE_REPRES.ID_CLI = PESSOA.ID_PES) ";
      $sql .= " AND PESSOA.INATIVO = 0 ";

      if ( $idRepres > 0 ) {
        $sql .= " AND PERFIL_CLIENTE_REPRES.ID_REP = " . $idRepres ;
      }

      $sql .= " GROUP BY PESSOA.ID_PES ";
      $sql .= " HAVING (MAX(PESSOA.DT_ULT_PED) <  DATEADD(MONTH, -3, CURRENT_DATE - EXTRACT(DAY FROM CURRENT_DATE) + 1) OR MAX(PESSOA.DT_ULT_PED) IS NULL)) ";

      $sql .= " SELECT COUNT(*) QUANTIDADE FROM CLIENTES ";

      return $sql;

    }

    function get( $idRepres ) {

      $sql = $this->setCommandSQL( $idRepres );

      $query = $this->db->query( $sql );
      $rows  = $query->result();

      $data = array();
      if ( $query->num_rows() > 0 ) {
        foreach( $rows as $row ) {
          array_push( $data, array(
            'Quantidade' => $row->QUANTIDADE 
            )
          );
        }
      }
      return $data;
    }

  }
