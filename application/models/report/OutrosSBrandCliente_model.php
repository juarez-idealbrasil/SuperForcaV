<?php

  class OutrosSBrandCliente_model extends CI_Model {
            
    function __construct() {
      parent::__construct();
      $this->load->model('Report/OutrosSBrandClienteSBrand_model');
      $this->load->model('Report/OutrosSBrandClienteRealizado_model');
    }

    function get( $idRepres, $idEquipe, $dataIni, $dataFim, $mode ) {

      $sbrands   = $this->OutrosSBrandClienteSBrand_model->get( $idRepres, $idEquipe, $mode );
      $customers = $this->OutrosSBrandClienteRealizado_model->get( $idRepres, $idEquipe, $dataIni, $dataFim, $mode );  

      foreach( $customers as &$customer ) {

        foreach( $sbrands as $sbrand ) {

          $found = false;
          for ( $i = 1; $i < count( $customer ); $i++ ) { 

            if ( $customer[$i]['idSBrand'] == $sbrand['idSBrand'] ) {

              $customer[$i]['Descricao'] = $sbrand['Descricao'];
              $found = true;
              break;

            }

          }

          if (!$found) {

            $customer[] = array( 'idSBrand' => $sbrand['idSBrand'], 'Valor' => 0.00, 'Descricao' => $sbrand['Descricao'] );

          }

          usort( $customer, function( $a, $b ) {

            return $a <=> $b;

          });

        }

      }

      return $customers;

    }   

  }