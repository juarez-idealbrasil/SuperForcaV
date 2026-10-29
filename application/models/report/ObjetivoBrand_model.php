<?php

  class ObjetivoBrand_model extends CI_Model {
            
    function __construct() {
      parent::__construct();
      $this->load->model('Report/ObjetivoBrandObjetivo_model');
      $this->load->model('Report/ObjetivoBrandRealizado_model');
    }

    function get( $idRepres, $idEquipe, $dataIni, $dataFim, $mode ) {

      $totRealizado  = 0.0;
      $totObjetivo   = 0.0;
      $totSaldo      = 0.0;
      $totPositivado = 0.0;

      $objetivos  = $this->ObjetivoBrandObjetivo_model->get( $idRepres, $idEquipe, $mode );
      $realizados = $this->ObjetivoBrandRealizado_model->get( $idRepres, $idEquipe, $dataIni, $dataFim, $mode );

      foreach ($objetivos as &$objetivo){
        
        $idBrandO          = $objetivo['idBrand'];
        $valObjetivo       = (float)$objetivo['Objetivo'];
        $objetivo['Saldo'] = (-1)*$valObjetivo;
        
        foreach ($realizados as $realizado){

          $idBrandR     = $realizado['idBrand'];
          $valRealizado = (float)$realizado['Realizado'];          
          
          if ($idBrandO == $idBrandR) {

            $totRealizado += $valRealizado;
            $valSaldo      = $valRealizado - (float)$valObjetivo;
            $valPositivado = $valRealizado > 0 && $valObjetivo > 0 ? ($valRealizado / $valObjetivo) * 100 : 0.0;

            $objetivo['Realizado']  = $valRealizado;
            $objetivo['Saldo']      = (float)$valSaldo;
            $objetivo['Positivado'] = $valPositivado;

            break;

          }

        }

        $totObjetivo  += $valObjetivo;
        $totSaldo      = $totRealizado - $totObjetivo;
        $totPositivado = $totRealizado > 0 && $totObjetivo > 0 ? ($totRealizado / $totObjetivo) * 100 : 0.0;

      }

      $response['body']                 = $objetivos;
      $response['total']['Realizado']   = $totRealizado;
      $response['total']['Objetivo']    = $totObjetivo;
      $response['total']['Saldo']       = $totSaldo;
      $response['total']['Positivado']  = $totPositivado;

      return $response;

    }   

  }