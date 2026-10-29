<?php

class ClienteNPositivado_model extends CI_Model
{

	private $mBaseReport;

	function __construct() {
		parent::__construct();
		$this->load->model('Report/BaseReport_model');
		$this->mBaseReport = new BaseReport_model();
	}

	private function setCommandSQL( $idRepres, $months ) {

		$sql = " WITH CLIENTES AS ( ";
		$sql .= " SELECT  PESSOA.ID_PES, ";
		$sql .= "         PESSOA.RAZ_SOC, ";
		$sql .= "         MAX(PESSOA.DT_ULT_PED) MAX_PED ";
		$sql .= " FROM PESSOA ";
		$sql .= " JOIN PERFIL_CLIENTE_REPRES ON (PERFIL_CLIENTE_REPRES.ID_CLI = PESSOA.ID_PES) ";
		$sql .= " AND PESSOA.INATIVO = 0 ";

		if ($idRepres > 0) {
			$sql .= " AND PERFIL_CLIENTE_REPRES.ID_REP = " . $idRepres;
		}

		$sql .= " GROUP BY PESSOA.ID_PES, PESSOA.RAZ_SOC ";
		$sql .= " HAVING (MAX(PESSOA.DT_ULT_PED) <  DATEADD(MONTH, - $months, CURRENT_DATE - EXTRACT(DAY FROM CURRENT_DATE) + 1) OR MAX(PESSOA.DT_ULT_PED) IS NULL)) ";

		return $sql;

	}

	private function setCommandSQLCount( $idRepres, $months )	{

		$sql = $this->setCommandSQL( $idRepres, $months );

		$sql .= " SELECT COUNT(*) QUANTIDADE FROM CLIENTES ";

		return $sql;

	}

	function get( $idRepres, $months ) {

		$sql = $this->setCommandSQL( $idRepres, $months );

		$sql .= " SELECT * FROM CLIENTES ";

		$query = $this->db->query( $sql );
		$rows = $query->result();

		$data['body'] = array();
		if ( $query->num_rows() > 0 ) {
			foreach ( $rows as $row ) {
				array_push( $data['body'], array(
						'Id'        => $row->ID_PES,
						'RazSocial' => iconv("Windows-1252", "utf-8//IGNORE", $row->RAZ_SOC),
						'LastOrder' => $row->MAX_PED
					)
				);
			}
			$data['quantity'] = count($rows);
			$data['months'] = $months;
		}
		return $data;
	}

	function quantity( $idRepres, $months ) {

		$sql = $this->setCommandSQLCount( $idRepres, $months );

		$query = $this->db->query($sql);
		$rows = $query->result();

		$data = array();
		if ($query->num_rows() > 0) {
			foreach ($rows as $row) {
				array_push($data, array(
						'Quantidade' => $row->QUANTIDADE
					)
				);
			}
		}
		return $data;
	}
}
