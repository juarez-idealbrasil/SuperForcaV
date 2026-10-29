<?php

  class Connected_model extends CI_Model
  {
    public $ERRO_CONEXAO = "Erro de Conexão";
        
    function __construct() {
        parent::__construct();
    }

    function getConnected() {
      return 1;
    }

  }
