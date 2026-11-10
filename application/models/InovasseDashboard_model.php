<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class InovasseDashboard_model extends CI_Model
{
    const ROW_LIMIT = 500;

    public function search($view, $startDate = NULL, $endDate = NULL)
    {
        $table = $view['table'];
        $sql = 'SELECT FIRST ' . self::ROW_LIMIT . ' * FROM ' . $table;
        $params = array();

        if ($startDate !== NULL && $endDate !== NULL) {
            $endExclusive = new DateTime($endDate);
            $endExclusive->modify('+1 day');

            $sql .= ' WHERE ' . $view['date_column'] . ' >= ? AND ' . $view['date_column'] . ' < ?';
            $params = array($startDate, $endExclusive->format('Y-m-d'));
        }

        $query = $this->db->query($sql, $params);
        if ($query === FALSE) {
            $databaseError = $this->db->error();
            $message = isset($databaseError['message']) ? $databaseError['message'] : 'Erro de consulta';
            log_message('error', 'Falha ao consultar ' . $table . ' no dashboard Inovasse: ' . $message);

            return array(
                'columns' => array(),
                'rows' => array(),
                'totals' => array(),
                'operation_totals' => array(),
                'operation_quantities' => array(),
                'canceled_summary' => NULL,
                'error' => 'Não foi possível consultar a view selecionada. Verifique sua configuração e consulte o log da aplicação.'
            );
        }

        $columns = $query->list_fields();
        $rows = $query->result_array();
        $totals = array();
        $operationTotals = array();
        $operationQuantities = array();
        $canceledSummary = array(
            'count' => 0,
            'quantity' => 0,
            'amount' => 0
        );
        $totalFields = array('QUANTIDADE', 'VALORTOTAL', 'VALORDESCONTO');
        $operationNames = array('ENTRADA', 'VENDA', 'BONIFICACAO', 'DEVOLUCAO', 'REMESSA');
        $operationCharacterMap = array(
            chr(0xC0) => 'A', chr(0xC1) => 'A', chr(0xC2) => 'A', chr(0xC3) => 'A', chr(0xC4) => 'A',
            chr(0xE0) => 'A', chr(0xE1) => 'A', chr(0xE2) => 'A', chr(0xE3) => 'A', chr(0xE4) => 'A',
            chr(0xC7) => 'C', chr(0xE7) => 'C',
            chr(0xC8) => 'E', chr(0xC9) => 'E', chr(0xCA) => 'E', chr(0xCB) => 'E',
            chr(0xE8) => 'E', chr(0xE9) => 'E', chr(0xEA) => 'E', chr(0xEB) => 'E',
            chr(0xCC) => 'I', chr(0xCD) => 'I', chr(0xCE) => 'I', chr(0xCF) => 'I',
            chr(0xEC) => 'I', chr(0xED) => 'I', chr(0xEE) => 'I', chr(0xEF) => 'I',
            chr(0xD2) => 'O', chr(0xD3) => 'O', chr(0xD4) => 'O', chr(0xD5) => 'O', chr(0xD6) => 'O',
            chr(0xF2) => 'O', chr(0xF3) => 'O', chr(0xF4) => 'O', chr(0xF5) => 'O', chr(0xF6) => 'O',
            chr(0xD9) => 'U', chr(0xDA) => 'U', chr(0xDB) => 'U', chr(0xDC) => 'U',
            chr(0xF9) => 'U', chr(0xFA) => 'U', chr(0xFB) => 'U', chr(0xFC) => 'U'
        );

        if (isset($view['date_column'])) {
            foreach ($totalFields as $field) {
                $totals[$field] = 0;
            }
            foreach ($operationNames as $operationName) {
                $operationTotals[$operationName] = 0;
                $operationQuantities[$operationName] = 0;
            }

            foreach ($rows as $row) {
                foreach ($row as $column => $value) {
                    $field = strtoupper(preg_replace('/[^A-Z0-9]/', '', $column));
                    if (isset($totals[$field]) && is_numeric($value)) {
                        $totals[$field] += (float) $value;
                    }
                }

                $operation = isset($row['NOME_OPERACAO'])
                    ? strtoupper(strtr(trim($row['NOME_OPERACAO']), $operationCharacterMap))
                    : '';
                $status = isset($row['STATUS'])
                    ? strtoupper(strtr(trim($row['STATUS']), $operationCharacterMap))
                    : '';
                $isCanceled = $status !== '' && $status !== 'EMITIDA';

                if (isset($operationTotals[$operation]) && !($operation === 'VENDA' && $isCanceled)) {
                    if (isset($row['QUANTIDADE']) && is_numeric($row['QUANTIDADE'])) {
                        $operationQuantities[$operation] += (float) $row['QUANTIDADE'];
                    }
                    if (isset($row['VALOR_TOTAL']) && is_numeric($row['VALOR_TOTAL'])) {
                        $operationTotals[$operation] += (float) $row['VALOR_TOTAL'];
                    }
                }

                if ($isCanceled) {
                    $canceledSummary['count']++;
                    if (isset($row['QUANTIDADE']) && is_numeric($row['QUANTIDADE'])) {
                        $canceledSummary['quantity'] += (float) $row['QUANTIDADE'];
                    }
                    if (isset($row['VALOR_TOTAL']) && is_numeric($row['VALOR_TOTAL'])) {
                        $canceledSummary['amount'] += (float) $row['VALOR_TOTAL'];
                    }
                }
            }
        }

        if ($canceledSummary['count'] === 0) {
            $canceledSummary = NULL;
        }

        return array(
            'columns' => $columns,
            'rows' => $rows,
            'totals' => $totals,
            'operation_totals' => $operationTotals,
            'operation_quantities' => $operationQuantities,
            'canceled_summary' => $canceledSummary,
            'error' => NULL
        );
    }
}
