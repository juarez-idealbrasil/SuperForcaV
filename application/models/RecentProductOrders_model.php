<?php

  class RecentProductOrders_model extends CI_Model
  {    
        
    function __construct() {
        parent::__construct();
    }

    function getRecentProductOrders($clientId, $productId) {     

      $sql = "SELECT * FROM SP_RECENT_PRODUCT_ORDERS(" . $clientId . "," . $productId . ")";      

      $query = $this->db->query($sql);
      $rows = $query->result();

      $data=array();
      if ($query->num_rows()>0) {
        foreach($rows as $row) {
          array_push($data, array(
            'Quantidade'=>$row->QUANT, 
            'Data'=>$row->EMISSAO));
        }
      }
      return $data;
    }

  }
