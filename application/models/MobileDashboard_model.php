<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MobileDashboard_model extends CI_Model
{
    public function getCompanies()
    {
        $query = $this->db->query(
            'SELECT ID_EMP, RAZ_SOC FROM EMPRESA ORDER BY EMP_DEF DESC, RAZ_SOC'
        );

        return $this->getRows($query, 'companies');
    }

    public function getFilterOptions()
    {
        return array(
            'teams' => $this->getRows(
                $this->db->query(
                    'SELECT ID_EQU AS ID_FILTRO, DESCRICAO AS NOME
                       FROM EQUIPE
                      WHERE INATIVO = 0
                      ORDER BY DESCRICAO'
                ),
                'teams'
            ),
            'representatives' => $this->getRows(
                $this->db->query(
                    'SELECT R.ID_PES AS ID_FILTRO, P.RAZ_SOC AS NOME
                       FROM REPRES R
                       JOIN PESSOA P ON P.ID_PES = R.ID_PES
                      WHERE P.INATIVO = 0
                      ORDER BY P.RAZ_SOC'
                ),
                'representatives'
            ),
            'customers' => $this->getRows(
                $this->db->query(
                    'SELECT C.ID_PES AS ID_FILTRO, P.RAZ_SOC AS NOME
                       FROM CLIENTE C
                       JOIN PESSOA P ON P.ID_PES = C.ID_PES
                      WHERE P.INATIVO = 0
                        AND P.TIPO_PESSOA = 0
                      ORDER BY P.RAZ_SOC'
                ),
                'customers'
            )
        );
    }

    public function getDashboard(
        $companyId,
        $startDate,
        $endDate,
        $previousStartDate,
        $previousEndDate,
        $representativeId = 0,
        $teamId = 0,
        $customerId = 0
    )
    {
        $commonParams = array(
            $companyId,
            $startDate,
            $endDate,
            $representativeId,
            $teamId,
            $customerId
        );

        return array(
            'indicators' => $this->getFirstRow(
                'SELECT * FROM SP_INDICADORES_GERAIS(?, ?, ?, ?, ?, ?)',
                $commonParams,
                'indicators'
            ),
            'daily_sales' => $this->getRows(
                $this->db->query(
                    'SELECT * FROM SP_VENDAS_DIARIAS(?, ?, ?, ?, ?, ?)',
                    $commonParams
                ),
                'daily sales'
            ),
            'daily_sales_previous' => $this->getRows(
                $this->db->query(
                    'SELECT * FROM SP_VENDAS_DIARIAS(?, ?, ?, ?, ?, ?)',
                    array(
                        $companyId,
                        $previousStartDate,
                        $previousEndDate,
                        $representativeId,
                        $teamId,
                        $customerId
                    )
                ),
                'previous-year daily sales'
            ),
            'rankings' => array(
                'products' => $this->getRows(
                    $this->db->query(
                        'SELECT * FROM SP_RANKING_VENDAS(?, ?, ?, ?, ?, ?, ?)',
                        array(
                            $companyId,
                            $startDate,
                            $endDate,
                            0,
                            $representativeId,
                            $teamId,
                            $customerId
                        )
                    ),
                    'product rankings'
                ),
                'salespeople' => $this->getRows(
                    $this->db->query(
                        'SELECT * FROM SP_RANKING_VENDAS(?, ?, ?, ?, ?, ?, ?)',
                        array(
                            $companyId,
                            $startDate,
                            $endDate,
                            1,
                            $representativeId,
                            $teamId,
                            $customerId
                        )
                    ),
                    'salesperson rankings'
                ),
                'suppliers' => $this->getRows(
                    $this->db->query(
                        'SELECT * FROM SP_RANKING_VENDAS(?, ?, ?, ?, ?, ?, ?)',
                        array(
                            $companyId,
                            $startDate,
                            $endDate,
                            2,
                            $representativeId,
                            $teamId,
                            $customerId
                        )
                    ),
                    'supplier rankings'
                )
            ),
            'customers' => $this->getRows(
                $this->db->query(
                    'SELECT * FROM SP_ANALISE_CLIENTE(?, ?, ?, ?, ?, ?)',
                    $commonParams
                ),
                'customer analysis'
            )
        );
    }

    private function getFirstRow($query, $params, $operation)
    {
        return $this->getRows($this->db->query($query, $params), $operation, TRUE);
    }

    private function getRows($query, $operation, $firstRow = FALSE)
    {
        if ($query === FALSE) {
            $error = $this->db->error();
            log_message(
                'error',
                'Mobile dashboard ' . $operation . ' query failed: ' . $error['message']
            );
            throw new RuntimeException('Mobile dashboard query failed.');
        }

        $rows = $query->result_array();
        foreach ($rows as &$row) {
            foreach (array('RAZ_SOC', 'DESCRICAO', 'CLIENTE_NOME', 'NOME') as $field) {
                if (isset($row[$field])) {
                    $row[$field] = $this->toUtf8($row[$field]);
                }
            }
        }
        unset($row);

        if ($firstRow) {
            return count($rows) > 0 ? $rows[0] : array();
        }

        return $rows;
    }

    private function toUtf8($value)
    {
        $converted = iconv('Windows-1252', 'UTF-8//IGNORE', $value);
        return $converted === FALSE ? $value : $converted;
    }
}
