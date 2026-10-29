<?php

  class ForcaVRelease_model extends CI_Model
  {    
        
    function __construct() {
        parent::__construct();
        $this->load->helper('custom_exception');
    }

    function getRelease($represId, $selectAll) {

      $sql = "SELECT * FROM SEL_FV_VERSION_RELEASE(" . $represId . ", " . (int)$selectAll . ")";

      $query = $this->db->query($sql);
      $rows = $query->result();

      $data=array();
      if ($query->num_rows()>0) {
        foreach($rows as $row) {
          array_push($data, array(
            'IdRepres'=>$row->ID_REP, 
            'RazSocial'=>iconv("Windows-1252", "utf-8//IGNORE", $row->RAZ_SOC), 
            'FVVersionCurrent'=>$row->FV_VERSION_CURRENT, 
            'FVVersionNext'=>$row->FV_VERSION_NEXT, 
            'FVVersionLink'=>$row->FV_VERSION_LINK,
            'FVVersionNote'=>iconv("Windows-1252", "utf-8//IGNORE", $row->FV_VERSION_NOTE),
            'FVVersionLength'=>$row->FV_VERSION_LENGTH) 
          );
        }
      }
      return $data;
    }

    function setFVVersion( $content ) {

      $data     = json_decode( $content );

      $response = array('status'           => 200, 
                        'code'             => 0, 
                        'httpcode'         => 200,
                        'message'          => null, 
                        'extended_message' => null
                        );

      try {

        $this->db->trans_begin();

        $query = " UPDATE PESSOA SET FV_CURRENT_VERSION = ?, FV_LAST_CONN = ? WHERE ID_PES = ? ";
        $date = new DateTime();

        $param = array( $data->version, 
                        $date->format('m/d/Y H:i'),
                        $data->represId );

        $this->db->query( $query, $param );

        $error = $this->db->error();

        if ( $error['code'] != 0 ) {
            throw new CustomException($error['message'], 
                                      "Nao foi possivel gravar dados da versão do app",
                                      $error['code']);
        }

        $this->db->trans_commit();

      } catch ( CustomException $e ) {    

        $this->db->trans_rollback();

        $response = array('status'           => 400, 
                          'code'             => -1, 
                          'httpcode'         => 200,
                          'message'          => $e->getMessage(), 
                          'extended_message' => $e->getExtendedMessage()
                          );

      }

      return $response;      

    }

    function setRelease( $content ) {

      $data     = json_decode( $content );

      $response = array('status'           => 200, 
                        'code'             => 0, 
                        'httpcode'         => 200,
                        'message'          => null, 
                        'extended_message' => null
                        );

      try {


        $this->db->trans_begin();

        $where = $data->IdRepres > 0 ? '=?' : '>?';

        $query = ' UPDATE PESSOA SET FV_VERSION_NEXT=?, FV_VERSION_LINK=?, FV_VERSION_NOTE=?, FV_VERSION_LENGTH=? '.
                 ' WHERE PESSOA.INATIVO = 0 AND PESSOA.ID_PES IN (SELECT ID_PES FROM REPRES WHERE FORC_VEND = 1) '.
                 ' AND ID_PES'.$where;                 

        $param = array( $data->FVVersionNext, 
                        $data->FVVersionLink, 
                        $data->FVVersionNote, 
                        $data->FVVersionLength, 
                        $data->IdRepres);

        $this->db->query( $query, $param );

        $this->db->trans_commit();

      } catch ( CustomException $e ) {

        $this->db->trans_rollback();

        $response = array('status'           => 400, 
                          'code'             => -1, 
                          'httpcode'         => 200,
                          'message'          => $e->getMessage(), 
                          'extended_message' => $e->getExtendedMessage()
                          );

      }

      return $response;      

    }

  }
