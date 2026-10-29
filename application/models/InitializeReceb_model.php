<?php

  class InitializeReceb_model extends CI_Model
  {
        
    function __construct() {
        parent::__construct();
    }

    function execInitializeReceb($id, $table) {

      try {

        $sql = "EXECUTE PROCEDURE SET_REPLICANDO(" . $id . ", '" . $table . "')";

        if ( !$this->db->query( $sql ) ) {
          $error = $this->db->error();
          return array( 'httpcode'         => 404, 
                        'code'             => -1, 
                        'message'          => 'Não foi possivel inicializar sincronização da tabela: '.$table, 
                        'extended_message' => $error['message'] 
                      );
        }else{
          return array( 'httpcode'=> 200, 
                        'code'    => false, 
                        'message' => 'Tabela "'.$table.'" inicializada com sucesso', 
                        'extended_message' => null
                      );
        }

      } catch ( Exception $e ) {

          return array( 'httpcode'=> 404, 
                        'code'    => -1, 
                        'message' => 'Erro ao inicializar recebimento da tabela: '.$table, 
                        'extended_message' => $e->getMessage()
                      );
      }

    }

  }
