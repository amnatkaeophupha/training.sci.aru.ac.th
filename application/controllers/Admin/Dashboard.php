<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @property CI_Loader $load
 * @property CI_Session $session
 */
class Dashboard extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');

        if (!$this->session->userdata('admin_logged_in')) {
            redirect('admin');
        }
    }

    public function index()
    {
        $this->load->view('admins/dashboard');
    }
}
