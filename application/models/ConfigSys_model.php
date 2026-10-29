<?php

  class ConfigSys_model extends CI_Model
  {
            
    function __construct() {
        parent::__construct();
    }

    function getConfigSys($represId) {

      $sql = "SELECT * FROM SEL_CONFIG_SYS(" . $represId . ")";

      $query = $this->db->query($sql);
      $rows = $query->result();

      $data=array();
      if ($query->num_rows()>0) {
        foreach($rows as $row) {
          array_push($data, array(            
            'Id'=>$row->ID_CFG,
            'LiberaCampoDesc'=>$row->LIB_CAMP_DCT,
            'LiberaCampoPreco'=>$row->LIB_CAMP_PRC,
            'RecalculaItem'=>$row->RECALC_ITEM,
            'CalculaSugNeg'=>$row->CALC_SUG_NEG,
            'PrazoMaxCobranca'=>$row->PRAZO_MAX_COB,
            'PercSugNeg'=>$row->PERC_SUG_NEG,
            'AplicaSugNegPedido'=>$row->APL_SUG_NEG_PED,
            'MaxDiasFaturado'=>$row->MAX_DIAS_FAT,
            'MaxDiasAberto'=>$row->MAX_DIAS_AB,
            'Aviso'=>iconv("Windows-1252", "utf-8//IGNORE", $row->AVISO),
            'QuantPedidoAberto'=>$row->QUANT_PED_AB,
            'QuantItemPedido'=>$row->QUANT_ITEM_PED,
            'OrderExibeProduto'=>$row->ORDER_PROD_FV,
            'BloqueioPedMinimo'=>$row->BLOQ_PED_MIN_FV,
            'AccessLocationRequired'=>$row->ACCESS_LOCATION_ADDRESS,		// deprecated
            'AccessLocationAddress'=>$row->ACCESS_LOCATION_ADDRESS,
            'MgrPrcMinMKT'=>$row->MGR_PRC_MIN_MKT,
            'RepositorioWebImage'=>$row->WEB_IMAGE,
			      'WhatsAppSupport'=>isset($row->WHATS_APP_SUPPORT) ? $row->WHATS_APP_SUPPORT : 0,
            'TipoOper'=>$row->OPER_TYPE,
            'CsFixaFV'=>$row->CS_FIXA_FV));
        }
      }
      return $data;
    }

  }
