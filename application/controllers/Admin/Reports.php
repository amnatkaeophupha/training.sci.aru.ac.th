<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @property CI_Loader $load
 * @property CI_Session $session
 * @property Report_model $Report_model
 */
class Reports extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->model('Report_model');

        if (!$this->session->userdata('admin_logged_in')) {
            redirect('admin');
        }
    }

    public function index()
    {
        $this->load->view('admins/reports', array(
            'overview' => $this->Report_model->get_overview(),
            'course_report' => $this->Report_model->get_course_report(),
            'payment_report' => $this->Report_model->get_payment_report(),
            'recent_registrations' => $this->Report_model->get_recent_registrations(10),
            'tables_ready' => $this->Report_model->tables_ready()
        ));
    }
}
