<?php

  class OutrosSBrandClienteSBrand_model extends CI_Model {

    private $mBaseReport;
            
    function __construct() {
        parent::__construct();
        $this->load->model('Report/BaseReport_model');
        $this->mBaseReport = new BaseReport_model();
    }

    private function setCommandSQL() {

      $sql  = ' SELECT SUB_BRAND.ID_SBR, ' ;
      $sql .= '        SUB_BRAND.ABREVIADO DESCRICAO ';
      $sql .= ' FROM SUB_BRAND ';
      $sql .= ' WHERE STRLEN(SUB_BRAND.ABREVIADO) > 0 AND INATIVO = 0 ';

      $sql .= ' ORDER BY SUB_BRAND.ABREVIADO ';

      return $sql;

    }

    function get( $idRepres, $idEquipe, $reportMode ) {

      $sql = $this->setCommandSQL();

      $query = $this->db->query( $sql );
      $rows  = $query->result();

      $data = array();
      if ( $query->num_rows() > 0 ) {
        foreach( $rows as $row ) {
          array_push( $data, array(
            'idSBrand'  => $row->ID_SBR,
            'Descricao' => iconv("Windows-1252", "utf-8//IGNORE", $row->DESCRICAO)
            )
        );
        }
      }
      return $data;
    }   

  }