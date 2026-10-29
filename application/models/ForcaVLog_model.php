<?php

  class ForcaVLog_model extends CI_Model
  {    
        
    function __construct() {
        parent::__construct();
      $this->load->helper('log_manager_helper');
      

    }

    function getLog($represId, $dateSent) {

      $sql = "SELECT * FROM SEL_FV_LOG(" . $represId . ", '" . $dateSent . "')";

      LogManager::Message( $sql ) ;

      $query = $this->db->query($sql);
      $rows = $query->result();

      $data=array();
      if ($query->num_rows()>0) {
        foreach($rows as $row) {
          array_push($data, array(
            'IdRepres'=>$row->ID_REP, 
            'RazSocial'=>iconv("Windows-1252", "utf-8//IGNORE", $row->RAZ_SOC), 
            'TextLog'=>$row->TEXT_LOG, 
            'FVVersion'=>$row->VERSAO_FV, 
            'DateSent'=>$row->DATE_SENT) 
          );
        }
      }
      return $data;
    }

    function setLog( $content ) {

      $data     = json_decode( $content );

      $response = array('status'           => 200, 
                        'code'             => 0, 
                        'httpcode'         => 200,
                        'message'          => null, 
                        'extended_message' => null
                        );

      try {

        $this->db->trans_begin();

        $query = 'INSERT INTO FV_REPLICATE_LOG (ID_REP, TEXT_LOG, VERSAO_FV) VALUES (?, ?, ?)';

        $param = array( $data->represId,
                        $data->text,
                        $data->version); 

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
