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
            'course_id' => (int) $this->input->get('course_id', TRUE),
            'batch_id' => (int) $this->input->get('batch_id', TRUE),
            'status' => (int) $this->input->get('status', TRUE),
            'q' => trim((string) $this->input->get('q', TRUE))
        );

        $batches = $filters['course_id'] > 0
            ? $this->Registration_model->get_filter_batches($filters['course_id'])
            : array();

        $valid_batch = FALSE;
        foreach ($batches as $batch) {
            if ((int) $batch->id === $filters['batch_id']) {
                $valid_batch = TRUE;
                break;
            }
        }

        if ($filters['batch_id'] > 0 && !$valid_batch) {
            $filters['batch_id'] = 0;
        }

        if (!$this->session->userdata('registration_delete_token')) {
            $this->session->set_userdata('registration_delete_token', bin2hex(random_bytes(32)));
        }

        $this->load->view('admins/registrations', array(
            'delete_token' => $this->session->userdata('registration_delete_token'),
            'registrations' => $this->Registration_model->get_all($filters),
            'stats' => $this->Registration_model->get_stats($filters),
            'filters' => $filters,
            'courses' => $this->Registration_model->get_filter_courses(),
            'batches' => $batches,
            'filter_context' => $this->Registration_model->get_filter_context($filters['course_id'], $filters['batch_id']),
            'tables_ready' => $this->Registration_model->tables_ready()
        ));
    }

    public function delete($id)
    {
        if (!admin_require_delete_permission($this->session, 'admin/registrations')) return;

        if ($this->input->method(TRUE) !== 'POST') {
            show_404();
            return;
        }

        // Rebuild only local, known filters; never accept a client return URL.
        $filters = array();
        foreach (array('course_id', 'batch_id', 'status') as $key) {
            $filters[$key] = max(0, (int) $this->input->post($key, TRUE));
        }
        $filters['q'] = trim((string) $this->input->post('q', TRUE));
        $return_url = 'admin/registrations?'.http_build_query($filters);
        $token = $this->input->post('delete_token');
        $expected = $this->session->userdata('registration_delete_token');

        if (!is_string($token) || !is_string($expected) || $expected === '' || !hash_equals($expected, $token)) {
            $this->session->set_flashdata('error', 'คำขอไม่ถูกต้อง กรุณาโหลดหน้าใหม่แล้วลองอีกครั้ง');
        } elseif (!($registration = $this->Registration_model->get_by_id($id))) {
            $this->session->set_flashdata('error', 'ไม่พบข้อมูลผู้ลงทะเบียน หรือรายการถูกลบไปแล้ว');
        } elseif (admin_registration_delete_locked($registration->status, $registration->payment_status)) {
            $this->session->set_flashdata('error', 'ไม่สามารถลบใบสมัครที่ชำระเงินแล้ว และมีสถานะอนุมัติแล้วหรือเข้าอบรมแล้ว');
        } else {
            $files = $this->Registration_model->delete_registration((int) $id);
            if ($files === FALSE) {
                $this->session->set_flashdata('error', 'ไม่สามารถลบข้อมูลการลงทะเบียนได้ กรุณาลองอีกครั้ง');
            } elseif (!$this->Registration_model->delete_payment_files($files)) {
                $this->session->set_flashdata('error', 'ลบข้อมูลการลงทะเบียนแล้ว แต่ลบไฟล์ชำระเงินบางส่วนไม่สำเร็จ กรุณาติดต่อผู้ดูแลระบบ');
            } else {
                $this->session->set_flashdata('success', 'ลบข้อมูลการลงทะเบียนและเอกสารการชำระเงินเรียบร้อยแล้ว');
            }
        }
        redirect($return_url);
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
