<?php

  class OutrosBrandCliente_model extends CI_Model {
            
    function __construct() {
      parent::__construct();
      $this->load->model('Report/OutrosBrandClienteBrand_model');
      $this->load->model('Report/OutrosBrandClienteRealizado_model');
    }

    function get( $idRepres, $idEquipe, $dataIni, $dataFim, $mode ) {

      $brands    = $this->OutrosBrandClienteBrand_model->get( $idRepres, $idEquipe, $mode );
      $customers = $this->OutrosBrandClienteRealizado_model->get( $idRepres, $idEquipe, $dataIni, $dataFim, $mode );  

      foreach( $customers as &$customer ) {

        foreach( $brands as $brand ) {          

          $found = false;
          for ( $i = 1; $i < count( $customer ); $i++ ) { 

            if ( $customer[$i]['idBrand'] == $brand['idBrand'] ) {

              $customer[$i]['Descricao'] = $brand['Descricao'];
              $found = true;
              break;

            }

          }

          if (!$found) {

            $customer[] = array( 'idBrand' => $brand['idBrand'], 'Valor' => 0.00, 'Descricao' => $brand['Descricao'] );

          }

          usort( $customer, function( $a, $b ) {

            return $a <=> $b;

          });

        }

      }

      return $customers;

    }   

  }