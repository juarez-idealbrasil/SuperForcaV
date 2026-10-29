<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class DashboardEvolucaoRepres extends CI_Controller {

    function __construct() {
        // Construct the parent class
        parent::__construct();
    
        $this->load->model('Report/EvolucaoRepres_model');
        $this->load->model('ConfigReportFV_model');
    }        	

	public function evolucao()	{

        $response = $this->ConfigReportFV_model->getConfigReportFV( 'EVOL_PER_REP' );

        $idRepres = $_GET['id_repres'];
        $idEquipe = $_GET['id_equipe'];
        $dataIni  = $response['data_ini'];
        $dataFim  = $response['data_fim'];
        $mode     = $response['modo'];

        $response = $this->EvolucaoRepres_model->get( $idRepres, $idEquipe, $dataIni, $dataFim, $mode ) ;

		$data = array();      
        foreach( $response as $row ) {
          array_push( $data, array(
            $row['Janeiro'], 
            $row['Fevereiro'],
            $row['Marco'],
            $row['Abril'],
            $row['Maio'],
            $row['Junho'],
            $row['Julho'],
            $row['Agosto'],
            $row['Setembro'],
            $row['Outubro'],
            $row['Novembro'],
            $row['Dezembro'])
          );
        }      

        echo count( $data ) > 0 ? json_encode( $data[0] ) : '';

    }
}
