<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @property CI_Input $input
 * @property CI_Loader $load
 * @property CI_Session $session
 * @property Registration_model $Registration_model
 */
class Registrations extends CI_Controller
{
    private $registration_statuses = array(1, 2, 3, 4, 5);
    private $payment_statuses = array(1, 2, 3, 4);

    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->model('Registration_model');

        if (!$this->session->userdata('admin_logged_in')) {
            redirect('admin');
        }
    }

    public function index()
    {
        $filters = array(
            'status' => (int) $this->input->get('status', TRUE),
            'q' => trim((string) $this->input->get('q', TRUE))
        );

        $this->load->view('admins/registrations', array(
            'registrations' => $this->Registration_model->get_all($filters),
            'stats' => $this->Registration_model->get_stats(),
            'filters' => $filters,
            'tables_ready' => $this->Registration_model->tables_ready()
        ));
    }

    public function view($id)
    {
        $registration = $this->Registration_model->get_by_id($id);

        if (!$registration) {
            $this->session->set_flashdata('error', 'ไม่พบข้อมูลผู้ลงทะเบียน');
            redirect('admin/registrations');
            return;
        }

        $this->load->view('admins/registration_detail', array(
            'registration' => $registration,
            'participants' => $this->Registration_model->get_participants($id),
            'payments' => $this->Registration_model->get_payments($id)
        ));
    }

    public function update_status($id)
    {
        $registration = $this->Registration_model->get_by_id($id);

        if (!$registration) {
            $this->session->set_flashdata('error', 'ไม่พบข้อมูลผู้ลงทะเบียน');
            redirect('admin/registrations');
            return;
        }

        $status = (int) $this->input->post('status', TRUE);

        if (!in_array($status, $this->registration_statuses, TRUE)) {
            $this->session->set_flashdata('error', 'สถานะผู้ลงทะเบียนไม่ถูกต้อง');
            redirect('admin/registrations/view/'.(int) $id);
            return;
        }

        $this->Registration_model->update_status($id, $status);
        $this->session->set_flashdata('success', 'อัปเดตสถานะผู้ลงทะเบียนเรียบร้อยแล้ว');
        redirect('admin/registrations/view/'.(int) $id);
    }

    public function update_payment($registration_id, $payment_id)
    {
        $registration = $this->Registration_model->get_by_id($registration_id);

        if (!$registration) {
            $this->session->set_flashdata('error', 'ไม่พบข้อมูลผู้ลงทะเบียน');
            redirect('admin/registrations');
            return;
        }

        $status = (int) $this->input->post('status', TRUE);

        if (!in_array($status, $this->payment_statuses, TRUE)) {
            $this->session->set_flashdata('error', 'สถานะการชำระเงินไม่ถูกต้อง');
            redirect('admin/registrations/view/'.(int) $registration_id);
            return;
        }

        $this->Registration_model->update_payment_status($payment_id, $status);
        $this->session->set_flashdata('success', 'อัปเดตสถานะการชำระเงินเรียบร้อยแล้ว');
        redirect('admin/registrations/view/'.(int) $registration_id);
    }
}
