<?php

  class FinalizaReceb_model extends CI_Model
  {
        
    function __construct() {
        parent::__construct();
    }

    function execFinalizaReceb($id, $table) {

      try {

        $sql = "EXECUTE PROCEDURE SET_REPLICADO(" . $id . ", '" . $table . "')";

        if ( !$this->db->query( $sql ) ) {
          $error = $this->db->error();
          return array( 'httpcode'         => 404, 
                        'status'           => 404, 
                        'code'             => -1, 
                        'message'          => 'Não foi possivel finalizar sincronização da tabela: '.$table, 
                        'extended_message' => $error['message'] 
                      );
        }else{
          return array( 'httpcode'         => 200, 
                        'status'           => 200, 
                        'code'             => 0, 
                        'message'          => 'Tabela "'.$table.'" Finalizada com sucesso', 
                        'extended_message' => null
                      );
        }

      } catch ( Exception $e ) {

          return array( 'httpcode'         => 404, 
                        'status'           => 404, 
                        'code'             => -1, 
                        'message'          => 'Erro ao finalizar recebimento da tabela: '.$table, 
                        'extended_message' => $e->getMessage()
                      );
      }

    }

  }
