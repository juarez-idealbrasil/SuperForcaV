<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MobileAuth_model extends CI_Model
{
    public function authenticate($username, $password)
    {
        $query = $this->db->query(
            'SELECT FIRST 2 ID_USU, NOME FROM USUARIO WHERE NOME = ? AND SENHA = ? AND INATIVO = 0',
            array($username, $password)
        );

        if ($query === FALSE) {
            $error = $this->db->error();
            log_message('error', 'Mobile login query failed: ' . $error['message']);
            show_error('Não foi possível validar o acesso neste momento.', 500);
            return NULL;
        }

        if ($query->num_rows() !== 1) {
            return NULL;
        }

        return $query->row();
    }
}
