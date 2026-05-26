<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @property CI_Input $input
 * @property CI_Loader $load
 * @property CI_Session $session
 * @property Admin_model $Admin_model
 */
class Auth extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Admin_model');
    }

    public function login()
    {
        if ($this->session->userdata('admin_logged_in')) {
            redirect('admin/dashboard');
            return;
        }

        $this->load->view('admins/login');
    }

    public function check_login()
    {
        $username = $this->input->post('username', TRUE);
        $password = $this->input->post('password', TRUE);

        $admin = $this->Admin_model->get_by_username($username);

        if ($admin && password_verify($password, $admin->password)) {
            $this->session->set_userdata(array(
                'admin_id'        => $admin->id,
                'admin_name'      => $admin->name,
                'admin_username'  => $admin->username,
                'admin_role'      => $admin->role,
                'admin_logged_in' => TRUE
            ));

            redirect('admin/dashboard');
            return;
        }

        $this->session->set_flashdata('error', 'Username หรือ Password ไม่ถูกต้อง');
        redirect('admin');
    }

    public function logout()
    {
        $this->session->unset_userdata(array(
            'admin_id',
            'admin_name',
            'admin_username',
            'admin_role',
            'admin_logged_in'
        ));

        redirect('admin');
    }
}
