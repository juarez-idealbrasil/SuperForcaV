<?php

  class ConfigSys_model extends CI_Model {
            
    function __construct() {
        parent::__construct();
    }

    function get() {

      $sql  = "SELECT CONFIG_SYS.PERIODO, ";
      $sql .= "       CONFIG_SYS.AJUS_PERIODO, ";
      $sql .= "       CONFIG_SYS.REL_REP_FROM_CLIE, ";
      $sql .= "       CONFIG_SYS.REL_FATUR, ";
      $sql .= "       CONFIG_SYS.ADD_BON_REL ";
      $sql .= "FROM CONFIG_SYS";

      $row = $this->db->query($sql)->row();

      return array(
            'Periodo'             => $row->PERIODO,
            'AjustePeriodo'       => $row->AJUS_PERIODO, 
            'RepresFromCliente'   => boolval( $row->REL_REP_FROM_CLIE ), 
            'ReportByFatur'       => boolval( $row->REL_FATUR ), 
            'AddBonificReport'    => boolval( $row->ADD_BON_REL ),
            'SQLRepresFromCliente'=> boolval( $row->REL_REP_FROM_CLIE ) ? 'CLIENTE.ID_REP'                         : 'PEDIDO.ID_REP',
            'SQLReportByFatur'    => boolval( $row->REL_FATUR )         ? 'PEDIDO.FATURA'                          : 'PEDIDO.EMISSAO',
            'SQLAddBonificReport' => boolval( $row->ADD_BON_REL )       ? '(CFOP.CLASSIF = 1 OR CFOP.CLASSIF = 2)' : 'CFOP.CLASSIF = 1'
            );

    }

  }
