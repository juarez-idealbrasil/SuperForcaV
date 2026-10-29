<?php

class HistCliente_model extends CI_Model
{    
      
  function __construct() {
      parent::__construct();
  }

  function getHistCliente($clienteId, $respId) {

    $sql = "SELECT * FROM SEL_HIST_CLIENTE(" . $clienteId . ", " . $respId . ")";

    $query = $this->db->query($sql);
    $rows = $query->result();

    $data=array();
    if ($query->num_rows()>0) {
      foreach($rows as $row) {
        array_push($data, array(
            'Data'=>$row->DATA, 
            'Historico'=>$row->HISTORICO));
      }
    }
    return $data;
  }

  public function saveList( $content ) {

    $data     = json_decode( $content );
    $response = array('status'           => 200, 
      'code'             => 0, 
      'httpcode'         => 200,
      'message'          => null, 
      'extended_message' => null
    );

    try {

      $this->db->trans_begin();

      foreach ($data as $histCliente) {
        if ($histCliente->id_cliente > 0) {
          $this->save( $histCliente->id_cliente, $histCliente->descricao, $histCliente->respId );
        }

      }

      $this->db->trans_commit();

    } catch (Exception $e) {

      $this->db->trans_rollback();

      $response = array('status'           => 400, 
                        'code'             => -1, 
                        'httpcode'         => 200,
                        'message'          => $e->getMessage(), 
                        'extended_message' => $e->getExtendedMessage()
                        );
    } catch (CustomException $e) {

      $response = array('status'           => 400, 
                        'code'             => -1, 
                        'httpcode'         => 200,
                        'message'          => $e->getMessage(), 
                        'extended_message' => $e->getExtendedMessage()
                        );

    }

    

    return $response;

  }    

  function save($clienteId, $historico, $respId) {

    $query = "EXECUTE PROCEDURE SP_INS_HIST_CLIENTE(?, ?, ?);";
    $param = array($clienteId, 
                    $historico,
                    $respId
                  );
    
    $this->db->query($query, $param);

    $response = $this->db->error();

    if ( $response['code'] != 0 ) {
        throw new CustomException($response['message'], 
                                  "Nao foi possivel processar HistCliente:".
                                  $clienteId,
                                  $response['code']);
    }      

    return $response;
  }

}
