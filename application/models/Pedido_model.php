<?php

  class Pedido_model extends CI_Model
  {    

    const OperVenda      = 1;
    const OperBonific    = 2;
    const SQLCODE_803    = 'SQLCODE:-803';
    const SQLFOREIGN_KEY = 'FOREIGN KEY';
        
    function __construct() {
        parent::__construct();
        $this->load->helper('custom_exception');
        $this->load->helper('function_helper');
        //$this->load->helper('log_manager_helper');
      //LogManager::Message( '' ) ;
    }

    function getSQLCode($str) {
      return strpos( $str, self::SQLCODE_803 ) || strpos( $str, self::SQLFOREIGN_KEY ) ;
    }

    function getPedidoHash( $hashing ) {

      $sql = " SELECT FIRST 1 * FROM PEDIDO WHERE HASHING='$hashing' ";

      $query = $this->db->query($sql);
      $row = $query->row();

      $data = null;
      if ( isset( $row ) ) {
        $data = array(
            'id_ped'=>$row->ID_PED,
            'id_rep'=>$row->ID_REP,
            'id_cop'=>$row->ID_COP,
            'emissao'=>$row->EMISSAO,
            'hashing'=>$row->HASHING,
            'num_ped'=>$row->COD_COMPRA
          
        );        
      }
      return $data;
    }

    function getVendaHash( $hashing ) {

      $sql = "select FIRST 1 * from VENDA where HASHING='$hashing'";

      $query = $this->db->query($sql);
      $row = $query->row();

      $data = null;
      if ( isset( $row ) ) {
        $data = array(
            'id_ped'=>$row->ID_PED,
            'id_cpg'=>$row->ID_CPG,
            'id_fpg'=>$row->ID_FPG,
            'hashing'=>$row->HASHING
        );        
      }
      return $data;
    }

    function getItemHash( $hashing ) {

      $sql = "select FIRST 1 * from ITEM_PED where HASHING='$hashing'";

      $query = $this->db->query($sql);
      $row = $query->row();

      $data = null;
      if ( isset( $row ) ) {
        $data = array(
            'id_ipe'=>$row->ID_IPE,
            'id_ped'=>$row->ID_PED,
            'id_pro'=>$row->ID_PRO,
            'quant'=>$row->QUANT,
            'quant_solic'=>$row->QUANT_SOLIC,
            'preco_unit'=>$row->PRECO_UNIT,
            'preco_sug'=>$row->PRECO_SUG,
            'desconto'=>$row->DESCONTO,
            'hashing'=>$row->HASHING          
        );        
      }

      return $data;
    }

    private function insertPedido( $data ) {		

      /* creating hash */
      $data->pedido->hashing = hash('sha256', 
        $data->pedido->id_ped.
        $data->pedido->id_pes.
        $data->pedido->id_rep.
        $data->pedido->id_cop.
        $data->pedido->emissao);

      // Compatibilidade com a versão <= 6.0.16
      if ( !isset( $data->pedido->in_store ) ) {
		  $data->pedido->in_store = 0;
	  }

      if ( !isset( $data->pedido->total_mkt ) ) {
		  $data->pedido->total_mkt = 0;
	  }
	  
	  
      //if ( $data->pedido->id_rep == 47 ) {
      //  LogManager::Message( json_encode( $data ) ) ;
      //}
	  
	  
	  

      $query = "EXECUTE PROCEDURE SP_INS_PEDIDO(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);";
      $param = array($data->pedido->id_ped, 
                     $data->pedido->id_pes, 
                     $data->pedido->id_rep, 
                     $data->pedido->id_cop, 
                     $data->pedido->emissao, 
                     $data->pedido->fatura,
                     $data->pedido->status, 
                     $data->pedido->total_neg, 
                     $data->pedido->total_sug,
		  			 $data->pedido->total_mkt,
                     $data->pedido->observ_1,
                     $data->pedido->observ_2, 
                     $data->pedido->observ_3,
                     $data->pedido->num_ped,
                     $data->pedido->hashing,
                     $data->pedido->in_store
                   );

      /* check duplicate */
      $ret = $this->getPedidoHash( $data->pedido->hashing );

      if ( isset( $ret ) ) {

        $response['code']    = 0;
        $response['message'] = false;

      } else {

        $this->db->query($query, $param);

        $response = $this->db->error();

        /* duplicate Primary or Unique Key */
        if ( $response['code'] == -803 || $this->getSQLCode( $response['message'] ) ) {

          $response['code']    = 0;
          $response['message'] = false;

        }

      }     

      if ( $response['code'] != 0 ) {
          throw new CustomException($response['message'], 
                                    "Nao foi possivel processar o pedido (PEDIDO) numero:".
                                    $data->pedido->id_ped.
                                    " Codigo Interno: ".$data->pedido->num_ped.
                                    " Valor do Pedido: ".Functions::FloatFormat($data->pedido->total_neg),
                                    $response['code']);
      }      

      return $response;
    }

    private function insertVenda( $data ) {

      if ( $data->pedido->id_cop == self::OperBonific ){
        return;
      } 

      /* creating hash */
      $data->venda->hashing = hash('sha256', 
        $data->venda->id_ped.
        $data->venda->id_cpg.
        $data->venda->id_fpg);

      $query = "EXECUTE PROCEDURE SP_INS_VENDA(?, ?, ?, ?);";
      $param = array($data->venda->id_ped, 
                     $data->venda->id_cpg, 
                     $data->venda->id_fpg, 
                     $data->venda->hashing);

      /* check duplicate */
      $ret = $this->getVendaHash( $data->venda->hashing );

      if ( isset( $ret ) ) {

        $response['code']    = 0;
        $response['message'] = false;

      } else {

        $this->db->query($query, $param);

        $response = $this->db->error();

        /* duplicate Primary or Unique Key */
        if ( $response['code'] == -803 || $this->getSQLCode( $response['message'] ) ) {

          $response['code']    = 0;
          $response['message'] = false;

        }

      }

      if ( $response['code'] != 0 ) {
          throw new CustomException("Não foi possivel processar o pedido (VENDA) número:".
                                    $data->pedido->id_ped.
                                    " Código Interno: ".$data->pedido->num_ped.
                                    " Valor do Pedido: ".Functions::FloatFormat($data->pedido->total_neg), 
                                    $response['message'], 
                                    $response['code']);
      }      

      return $response;
    }

    private function insertBonific( $data ) {

      if ( $data->pedido->id_cop == self::OperVenda ){
        return;
      } 

      $query = "EXECUTE PROCEDURE SP_INS_BONIFIC(?);";
      $param = array($data->pedido->id_ped);

      $this->db->query($query, $param);

      $response = $this->db->error();

      /* duplicate Primary or Unique Key */
      if ( $response['code'] == -803 ) {

          $response['code']    = 0;
          $response['message'] = null;

      }

      if ( $response['code'] != 0 ) {
          throw new CustomException("Não foi possivel processar a bonificação número:".
                                    $data->pedido->id_ped.
                                    " Código Interno: ".$data->pedido->num_ped.
                                    " Valor do Pedido: ".Functions::FloatFormat($data->pedido->total_neg), 
                                    $response['message'], 
                                    $response['code']);
      }      

      return $response;
    }    

    private function insertItens( $data ) {

      $query = "EXECUTE PROCEDURE SP_INS_ITEM_PED(?, ?, ?, ?, ?, ?, ?, ?, ?);";

      foreach ($data->pedido->itens as $item) {

        /* creating hash */
        $item->hashing = hash('sha256', 
          $item->id_ipe.
          $item->id_ped.
          $item->id_pro.
          $item->quant.
          $item->preco_unit.
          $item->desconto);

        $param = array($item->id_ipe, 
                       $item->id_ped, 
                       $item->id_pro, 
                       $item->quant, 
                       $item->quant_solic, 
                       $item->preco_unit, 
                       $item->preco_sug, 
                       $item->desconto,
                       $item->hashing);

        /* check duplicate */
        $ret = $this->getItemHash( $item->hashing );

        if ( isset( $ret ) ) {

          $response['code']    = 0;
          $response['message'] = false;

        } else {

          $this->db->query($query, $param);

          $response = $this->db->error();

          /* duplicate Primary or Unique Key */
          if ( $response['code'] == -803 || $this->getSQLCode( $response['message'] ) ) {

            $response['code']    = 0;
            $response['message'] = false;

          }

        }

        if ( $response['code'] != 0 ) {

          throw new CustomException($response['message'], 
                                    "Não foi possivel processar o item do pedido(ITEM_PED) número:".
                                    $data->pedido->id_ped.
                                    " Código Interno: ".$data->pedido->num_ped.
                                    " Valor do Pedido: ".Functions::FloatFormat($data->pedido->total_neg),
                                    $response['code']);

        } 

      }
      return $response;
    }

    public function save( $content ) {

      $data = $content;

      $response = array('status'           => 200, 
                        'code'             => 0, 
                        'httpcode'         => 200,
                        'message'          => null, 
                        'extended_message' => null
                       );
      try {

        if ( !isset( $data->pedido ) ){
          throw new CustomException("Arquivo de pedido fora do padrao JSON",
                                    $content, 
                                    -1);
        } 

        if ( $data->pedido->id_cop == self::OperVenda && !isset( $data->venda ) ){
          throw new CustomException("Arquivo de pedido(VENDA) fora do padrao JSON",
                                    $content, 
                                    -1);
        } 

        $this->insertPedido( $data );
        $this->insertItens( $data );
        $this->insertVenda( $data );
        $this->insertBonific( $data );

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

    public function saveList( $content ) {

      $data     = json_decode( $content );
      $response = null;

      try {

        $this->db->trans_begin();

        foreach ($data as $pedido) {

          $response = $this->save( $pedido );

          if ( $response['status'] != 200) {

            throw new Exception($response['message']);

          }

        }

        $this->db->trans_commit();

      } catch (Exception $e) {

        $this->db->trans_rollback();

      }

      return $response;

    }    

  }
