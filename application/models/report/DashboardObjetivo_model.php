<?php

  class DashboardObjetivo_model extends CI_Model {
            
    function __construct() {
        parent::__construct();
        $this->load->model('Report/ObjetivoSubBrandRealizado_model');
        $this->load->model('Report/ObjetivoSubBrandObjetivo_model');
    }

    function get( $idRepres, $idEquipe, $dataIni, $dataFim, $mode ) {

        $objetivo  = $this->ObjetivoSubBrandObjetivo_model->getSum( $idRepres, $idEquipe, $mode );
        $realizado = $this->ObjetivoSubBrandRealizado_model->getSum( $idRepres, $idEquipe, $dataIni, $dataFim, $mode );

        $objetivo  = count( $objetivo )  > 0 ? (float)$objetivo[0]['Objetivo']   : 0;
        $realizado = count( $realizado ) > 0 ? (float)$realizado[0]['Realizado'] : 0;

        $data['Objetivo']      = $objetivo;
        $data['Realizado']     = $realizado;
        $data['RealizadoPerc'] = $realizado > 0 && $objetivo > 0 ? ($realizado / $objetivo) * 100 : 0;
        

        return $data;

    }

  }
