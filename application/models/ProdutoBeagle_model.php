<?php

  class ProdutoBeagle_model extends CI_Model
  {
            
    function __construct() {
        parent::__construct();
    }

    function get() {

      $sql = " SELECT EAN, ImagemPequena FROM Produto ";

      $query = $this->db->query($sql);
      $rows = $query->result();

      $data=array();
      if ($query->num_rows()>0) {
        foreach($rows as $row) {
          array_push($data, array(            
            'Ean'=>$row->EAN,
            'ImagemPequena'=>$row->ImagemPequena));
        }
      }
      return $data;
    }

  }
