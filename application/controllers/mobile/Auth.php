<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->helper(array('mobile', 'url', 'form'));
        $this->load->model('MobileAuth_model');
    }

    public function index()
    {
        if ($this->session->userdata('mobile_user_id')) {
            mobile_redirect('mobile/dashboard');
            return;
        }

        $data['error'] = '';

        if ($this->input->method(TRUE) === 'POST') {
            $username = $this->input->post('username', FALSE);
            $password = $this->input->post('password', FALSE);
            $user = NULL;

            if (is_string($username) && is_string($password)) {
                $user = $this->MobileAuth_model->authenticate(trim($username), $password);
            }

            if ($user !== NULL) {
                $this->session->sess_regenerate(TRUE);
                $this->session->set_userdata(array(
                    'mobile_user_id' => $user->ID_USU,
                    'mobile_user_name' => $user->NOME
                ));

                mobile_redirect('mobile/dashboard');
                return;
            }

            $data['error'] = 'Usuário ou senha inválidos.';
        }

        $this->load->view('mobile/login', $data);
    }

    public function logout()
    {
        if ($this->input->method(TRUE) !== 'POST') {
            show_404();
            return;
        }

        $this->session->unset_userdata(array('mobile_user_id', 'mobile_user_name'));
        $this->session->sess_regenerate(TRUE);
        mobile_redirect('mobile/auth');
    }
}
