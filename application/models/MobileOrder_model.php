<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MobileOrder_model extends CI_Model
{
    public function getRepresentatives()
    {
        $query = $this->db->query(
            'SELECT R.ID_PES, P.RAZ_SOC
               FROM REPRES R
               JOIN PESSOA P ON P.ID_PES = R.ID_PES
              WHERE P.INATIVO = 0
              ORDER BY P.RAZ_SOC'
        );

        return $this->mapRows($query, 'representative lookup', array('RAZ_SOC'));
    }

    public function searchCustomers($term, $representativeId = 0)
    {
        $pattern = $this->toDatabaseEncoding('%' . $term . '%');
        $representativeFilter = '';
        $params = array($pattern, $pattern, $pattern);
        if ((int) $representativeId > 0) {
            $representativeFilter = "
                AND EXISTS (
                    SELECT 1
                      FROM PERFIL_CLIENTE_REPRES PC
                     WHERE PC.ID_CLI = P.ID_PES
                       AND PC.ID_REP = ?
                )";
            $params[] = (int) $representativeId;
        }

        $query = $this->db->query(
            "SELECT FIRST 20
                    P.ID_PES,
                    P.RAZ_SOC,
                    P.FANTASIA,
                    P.CNPJ_CPF,
                    P.ID_TPR,
                    P.ID_CPG,
                    P.ID_FPG,
                    C.MAX_DIAS_CPG,
                    (SELECT FIRST 1 PC.ID_REP
                       FROM PERFIL_CLIENTE_REPRES PC
                       JOIN REPRES R ON R.ID_PES = PC.ID_REP
                       JOIN PESSOA RP ON RP.ID_PES = R.ID_PES AND RP.INATIVO = 0
                      WHERE PC.ID_CLI = P.ID_PES
                      ORDER BY COALESCE(PC.IS_DEFAULT, 0) DESC, PC.ID_PCR) AS ID_REP,
                    (SELECT FIRST 1 RP.RAZ_SOC
                       FROM PERFIL_CLIENTE_REPRES PC
                       JOIN REPRES R ON R.ID_PES = PC.ID_REP
                       JOIN PESSOA RP ON RP.ID_PES = R.ID_PES AND RP.INATIVO = 0
                      WHERE PC.ID_CLI = P.ID_PES
                      ORDER BY COALESCE(PC.IS_DEFAULT, 0) DESC, PC.ID_PCR) AS REPRESENTATIVE_NAME
               FROM PESSOA P
               JOIN CLIENTE C ON C.ID_PES = P.ID_PES
              WHERE P.INATIVO = 0
                AND COALESCE(C.INATIVO_FV, 0) = 0
                AND EXISTS (
                    SELECT 1
                      FROM PERFIL_CLIENTE_REPRES PC
                      JOIN REPRES R ON R.ID_PES = PC.ID_REP
                      JOIN PESSOA RP ON RP.ID_PES = R.ID_PES AND RP.INATIVO = 0
                     WHERE PC.ID_CLI = P.ID_PES
                )
                AND (UPPER(P.RAZ_SOC) LIKE UPPER(?)
                     OR UPPER(COALESCE(P.FANTASIA, '')) LIKE UPPER(?)
                     OR UPPER(P.CNPJ_CPF) LIKE UPPER(?))" . $representativeFilter . "
              ORDER BY P.RAZ_SOC",
            $params
        );

        return $this->mapRows($query, 'customer search', array(
            'RAZ_SOC',
            'FANTASIA',
            'REPRESENTATIVE_NAME'
        ));
    }

    public function getCustomer($customerId)
    {
        $query = $this->db->query(
            "SELECT FIRST 1
                    P.ID_PES,
                    P.RAZ_SOC,
                    P.FANTASIA,
                    P.CNPJ_CPF,
                    P.ID_TPR,
                    P.ID_CPG,
                    P.ID_FPG,
                    C.MAX_DIAS_CPG,
                    (SELECT FIRST 1 PC.ID_REP
                       FROM PERFIL_CLIENTE_REPRES PC
                       JOIN REPRES R ON R.ID_PES = PC.ID_REP
                       JOIN PESSOA RP ON RP.ID_PES = R.ID_PES AND RP.INATIVO = 0
                      WHERE PC.ID_CLI = P.ID_PES
                      ORDER BY COALESCE(PC.IS_DEFAULT, 0) DESC, PC.ID_PCR) AS ID_REP,
                    (SELECT FIRST 1 RP.RAZ_SOC
                       FROM PERFIL_CLIENTE_REPRES PC
                       JOIN REPRES R ON R.ID_PES = PC.ID_REP
                       JOIN PESSOA RP ON RP.ID_PES = R.ID_PES AND RP.INATIVO = 0
                      WHERE PC.ID_CLI = P.ID_PES
                      ORDER BY COALESCE(PC.IS_DEFAULT, 0) DESC, PC.ID_PCR) AS REPRESENTATIVE_NAME
               FROM PESSOA P
               JOIN CLIENTE C ON C.ID_PES = P.ID_PES
              WHERE P.ID_PES = ?
                AND P.INATIVO = 0
                AND COALESCE(C.INATIVO_FV, 0) = 0
                AND EXISTS (
                    SELECT 1
                      FROM PERFIL_CLIENTE_REPRES PC
                      JOIN REPRES R ON R.ID_PES = PC.ID_REP
                      JOIN PESSOA RP ON RP.ID_PES = R.ID_PES AND RP.INATIVO = 0
                     WHERE PC.ID_CLI = P.ID_PES
                )",
            array((int) $customerId)
        );
        $rows = $this->mapRows($query, 'customer lookup', array(
            'RAZ_SOC',
            'FANTASIA',
            'REPRESENTATIVE_NAME'
        ));

        return count($rows) > 0 ? $rows[0] : NULL;
    }

    public function searchProducts($customer, $term)
    {
        if ((int) $customer['ID_TPR'] < 1) {
            throw new DomainException('Este cliente não possui catálogo de preços configurado.');
        }

        $pattern = $this->toDatabaseEncoding('%' . $term . '%');
        $query = $this->db->query(
            "SELECT FIRST 30 DISTINCT
                    P.ID_PRO,
                    P.DESCRICAO,
                    P.BAR_CODE,
                    FP.PRECO
               FROM PERFIL_PRODUTO_REPRES PR
               JOIN PRODUTO P ON P.ID_PRO = PR.ID_PRO
               JOIN FORMACAO_PRECO FP
                 ON FP.ID_PRO = P.ID_PRO
                AND FP.CATALOG_ID = ?
              WHERE PR.ID_REP = ?
                AND P.INATIVO = 0
                AND FP.PRECO > 0
                AND (UPPER(P.DESCRICAO) LIKE UPPER(?) OR UPPER(P.BAR_CODE) LIKE UPPER(?))
              ORDER BY P.DESCRICAO",
            array(
                (int) $customer['ID_TPR'],
                (int) $customer['ID_REP'],
                $pattern,
                $pattern
            )
        );

        return $this->mapRows($query, 'product search', array('DESCRICAO'));
    }

    public function getProductsByIds($customer, $productIds)
    {
        if ((int) $customer['ID_TPR'] < 1 || count($productIds) === 0) {
            return array();
        }

        $placeholders = implode(',', array_fill(0, count($productIds), '?'));
        $params = array(
            (int) $customer['ID_TPR'],
            (int) $customer['ID_REP']
        );
        foreach ($productIds as $productId) {
            $params[] = (int) $productId;
        }

        $query = $this->db->query(
            "SELECT DISTINCT
                    P.ID_PRO,
                    P.DESCRICAO,
                    FP.PRECO
               FROM PERFIL_PRODUTO_REPRES PR
               JOIN PRODUTO P ON P.ID_PRO = PR.ID_PRO
               JOIN FORMACAO_PRECO FP
                 ON FP.ID_PRO = P.ID_PRO
                AND FP.CATALOG_ID = ?
              WHERE PR.ID_REP = ?
                AND P.INATIVO = 0
                AND FP.PRECO > 0
                AND P.ID_PRO IN (" . $placeholders . ')',
            $params
        );

        return $this->mapRows($query, 'order product validation', array('DESCRICAO'));
    }

    public function getPaymentOptions($customer)
    {
        $conditions = $this->mapRows(
            $this->db->query(
                'SELECT ID_CPG, DESCRICAO, MAX_DIAS, PEDIDO_MIN
                   FROM COND_PAG
                  WHERE INATIVO = 0
                    AND ALLOW_SELL_FV = 1
                  ORDER BY DESCRICAO'
            ),
            'payment condition lookup',
            array('DESCRICAO')
        );

        $paymentMethods = $this->mapRows(
            $this->db->query(
                'SELECT F.ID_FPG, F.DESCRICAO
                   FROM FORMA_PG F
                  WHERE F.INATIVO = 0
                    AND F.ALLOW_SELL_FV = 1
                    AND (
                        NOT EXISTS (
                            SELECT 1
                              FROM PERFIL_FORMA_PGTO PF
                             WHERE PF.ID_PES = ?
                        )
                        OR EXISTS (
                            SELECT 1
                              FROM PERFIL_FORMA_PGTO PF
                             WHERE PF.ID_PES = ?
                               AND PF.ID_FPG = F.ID_FPG
                        )
                    )
                  ORDER BY F.DESCRICAO',
                array((int) $customer['ID_PES'], (int) $customer['ID_PES'])
            ),
            'payment method lookup',
            array('DESCRICAO')
        );

        $maxDays = (int) $customer['MAX_DIAS_CPG'];
        $conditions = array_values(array_filter($conditions, function ($condition) use ($maxDays) {
            return $maxDays <= 0 || (int) $condition['MAX_DIAS'] <= $maxDays;
        }));

        return array(
            'conditions' => array_map(function ($condition) {
                return array(
                    'id' => (int) $condition['ID_CPG'],
                    'description' => $condition['DESCRICAO'],
                    'max_days' => (int) $condition['MAX_DIAS'],
                    'minimum_order' => (float) $condition['PEDIDO_MIN']
                );
            }, $conditions),
            'payment_methods' => array_map(function ($method) {
                return array(
                    'id' => (int) $method['ID_FPG'],
                    'description' => $method['DESCRICAO']
                );
            }, $paymentMethods),
            'default_condition_id' => (int) $customer['ID_CPG'],
            'default_payment_method_id' => (int) $customer['ID_FPG']
        );
    }

    public function createOrder($customerId, $items, $conditionId, $paymentMethodId, $orderHash)
    {
        if (!$this->db->trans_begin()) {
            $this->logDatabaseError('order transaction start');
            throw new RuntimeException('The order transaction could not be started.');
        }

        try {
            $existingOrderId = $this->getOrderIdByHash($orderHash);
            if ($existingOrderId !== NULL) {
                $this->commitTransaction();
                return array('id' => $existingOrderId, 'duplicate' => TRUE);
            }

            $customer = $this->getCustomer($customerId);
            if ($customer === NULL) {
                throw new DomainException('O cliente selecionado não está mais disponível.');
            }

            $productIds = array();
            foreach ($items as $item) {
                $productIds[] = (int) $item['id'];
            }
            $products = $this->getProductsByIds($customer, $productIds);
            $productsById = array();
            foreach ($products as $product) {
                $productsById[(int) $product['ID_PRO']] = $product;
            }

            if (count($productsById) !== count($productIds)) {
                throw new DomainException('Um ou mais produtos não estão disponíveis no catálogo deste cliente.');
            }

            $total = 0;
            foreach ($items as $item) {
                $product = $productsById[(int) $item['id']];
                $price = round((float) $product['PRECO'], 2);
                if (abs($price - (float) $item['price']) > 0.005) {
                    throw new DomainException(
                        'O preço de um produto foi atualizado. Revise os itens antes de salvar novamente.'
                    );
                }

                $quantity = (float) $item['quantity'];
                $total += round($price * $quantity, 2);
            }
            $total = round($total, 2);

            $paymentOptions = $this->getPaymentOptions($customer);
            $condition = $this->findById($paymentOptions['conditions'], $conditionId);
            $paymentMethod = $this->findById($paymentOptions['payment_methods'], $paymentMethodId);
            if ($condition === NULL || $paymentMethod === NULL) {
                throw new DomainException('A condição ou forma de pagamento não está disponível para este cliente.');
            }
            if ($total < $condition['minimum_order']) {
                throw new DomainException(
                    'O pedido precisa atingir o mínimo de ' . number_format($condition['minimum_order'], 2, ',', '.') . '.'
                );
            }

            $orderId = $this->getNextOrderId();
            $this->execute(
                'EXECUTE PROCEDURE SP_INS_PEDIDO(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);',
                array(
                    $orderId,
                    (int) $customer['ID_PES'],
                    (int) $customer['ID_REP'],
                    1,
                    date('Y-m-d'),
                    date('Y-m-d'),
                    99,
                    $total,
                    $total,
                    0,
                    '',
                    '',
                    '',
                    (string) $orderId,
                    $orderHash,
                    0
                ),
                'order header insert'
            );

            $storedOrderId = $this->getOrderIdByHash($orderHash);
            if ($storedOrderId === NULL) {
                throw new RuntimeException('The inserted order could not be read back.');
            }

            foreach ($items as $index => $item) {
                $product = $productsById[(int) $item['id']];
                $price = round((float) $product['PRECO'], 2);
                $itemHash = hash(
                    'sha256',
                    $orderHash . '|' . $storedOrderId . '|' . $item['id'] . '|' . $index
                );
                $this->execute(
                    'EXECUTE PROCEDURE SP_INS_ITEM_PED(?, ?, ?, ?, ?, ?, ?, ?, ?);',
                    array(
                        0,
                        $storedOrderId,
                        (int) $item['id'],
                        (float) $item['quantity'],
                        (float) $item['quantity'],
                        $price,
                        $price,
                        0,
                        $itemHash
                    ),
                    'order item insert'
                );
            }

            $this->execute(
                'EXECUTE PROCEDURE SP_INS_VENDA(?, ?, ?, ?);',
                array(
                    $storedOrderId,
                    (int) $condition['id'],
                    (int) $paymentMethod['id'],
                    hash('sha256', $orderHash . '|payment')
                ),
                'order payment insert'
            );

            $this->commitTransaction();
            return array(
                'id' => $storedOrderId,
                'duplicate' => FALSE,
                'total' => $total
            );
        } catch (Exception $exception) {
            $this->db->trans_rollback();
            throw $exception;
        }
    }

    private function findById($options, $id)
    {
        foreach ($options as $option) {
            if ((int) $option['id'] === (int) $id) {
                return $option;
            }
        }

        return NULL;
    }

    private function getNextOrderId()
    {
        $query = $this->db->query('SELECT GEN_ID(GEN_PED, 1) AS ID_PED FROM RDB$DATABASE');
        $rows = $this->mapRows($query, 'order number generation');
        if (count($rows) === 0 || (int) $rows[0]['ID_PED'] < 1) {
            throw new RuntimeException('The order number could not be generated.');
        }

        return (int) $rows[0]['ID_PED'];
    }

    private function getOrderIdByHash($orderHash)
    {
        $query = $this->db->query(
            'SELECT FIRST 1 ID_PED FROM PEDIDO WHERE HASHING = ?',
            array($orderHash)
        );
        $rows = $this->mapRows($query, 'order idempotency lookup');

        return count($rows) > 0 ? (int) $rows[0]['ID_PED'] : NULL;
    }

    private function execute($sql, $params, $operation)
    {
        $query = $this->db->query($sql, $params);
        if ($query === FALSE) {
            $this->logDatabaseError($operation);
            throw new RuntimeException('The ' . $operation . ' failed.');
        }

        return $query;
    }

    private function mapRows($query, $operation, $textFields = array())
    {
        if ($query === FALSE) {
            $this->logDatabaseError($operation);
            throw new RuntimeException('The ' . $operation . ' failed.');
        }

        $rows = $query->result_array();
        foreach ($rows as &$row) {
            foreach ($textFields as $field) {
                if (isset($row[$field])) {
                    $row[$field] = $this->toUtf8($row[$field]);
                }
            }
        }
        unset($row);

        return $rows;
    }

    private function logDatabaseError($operation)
    {
        $error = $this->db->error();
        log_message(
            'error',
            'Mobile order ' . $operation . ' failed: ' . $error['message']
        );
    }

    private function toUtf8($value)
    {
        if (!is_string($value) || function_exists('mb_check_encoding') && mb_check_encoding($value, 'UTF-8')) {
            return $value;
        }

        $converted = iconv('Windows-1252', 'UTF-8//IGNORE', $value);
        return $converted === FALSE ? $value : $converted;
    }

    private function toDatabaseEncoding($value)
    {
        $converted = iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $value);
        return $converted === FALSE ? $value : $converted;
    }

    private function commitTransaction()
    {
        if (!$this->db->trans_status()) {
            $this->logDatabaseError('order transaction');
            throw new RuntimeException('The order transaction failed.');
        }

        if (!$this->db->trans_commit()) {
            $this->logDatabaseError('order transaction commit');
            throw new RuntimeException('The order transaction could not be committed.');
        }
    }
}
