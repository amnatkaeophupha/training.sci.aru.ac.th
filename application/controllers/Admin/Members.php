<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Members extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->model('Member_model');

        if (!$this->session->userdata('admin_logged_in')) {
            redirect('admin');
        }
    }

    public function index()
    {
        $query = trim((string) $this->input->get('q', TRUE));
        $this->load->view('admins/members', array(
            'query' => $query,
            'members' => $this->Member_model->get_admin_list($query),
            'stats' => $this->Member_model->get_admin_stats()
        ));
    }

    public function store()
    {
        if ($this->input->method(TRUE) !== 'POST') show_404();
        $data = $this->post_data();
        $error = $this->validate_data($data, 0);
        if ($error !== '') return $this->fail($error);
        $this->Member_model->create($data);
        $this->session->set_flashdata('success', 'เพิ่มสมาชิกเรียบร้อยแล้ว');
        redirect('admin/members');
    }

    public function update($id)
    {
        if ($this->input->method(TRUE) !== 'POST') show_404();
        $id = (int)$id;
        if (!$this->Member_model->find_by_id($id)) return $this->fail('ไม่พบข้อมูลสมาชิก');
        $data = $this->post_data();
        $error = $this->validate_data($data, $id);
        if ($error !== '') return $this->fail($error);
        $this->Member_model->admin_update($id, $data);
        $this->session->set_flashdata('success', 'แก้ไขข้อมูลสมาชิกเรียบร้อยแล้ว');
        redirect('admin/members');
    }

    public function delete($id)
    {
        if ($this->input->method(TRUE) !== 'POST') show_404();
        if (!$this->Member_model->admin_delete((int)$id)) return $this->fail('ไม่สามารถลบสมาชิกที่มีประวัติการลงทะเบียนได้');
        $this->session->set_flashdata('success', 'ลบสมาชิกเรียบร้อยแล้ว');
        redirect('admin/members');
    }

    private function post_data()
    {
        return array(
            'title_name'=>trim((string)$this->input->post('title_name',TRUE)), 'first_name'=>trim((string)$this->input->post('first_name',TRUE)),
            'last_name'=>trim((string)$this->input->post('last_name',TRUE)), 'position_name'=>trim((string)$this->input->post('position_name',TRUE)),
            'organization_name'=>trim((string)$this->input->post('organization_name',TRUE)), 'email'=>trim((string)$this->input->post('email',TRUE)),
            'phone'=>trim((string)$this->input->post('phone',TRUE)), 'password'=>(string)$this->input->post('password'),
            'status'=>$this->input->post('status') === '0' ? 0 : 1
        );
    }

    private function validate_data($data, $id)
    {
        if ($data['first_name']==='' || $data['last_name']==='' || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) return 'กรุณากรอกชื่อ นามสกุล และอีเมลให้ถูกต้อง';
        if (!$id && strlen($data['password']) < 8) return 'รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร';
        if ($data['password'] !== '' && strlen($data['password']) < 8) return 'รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร';
        if ($id ? $this->Member_model->email_exists_except($data['email'],$id) : $this->Member_model->email_exists($data['email'])) return 'อีเมลนี้มีสมาชิกใช้งานแล้ว';
        return '';
    }

    private function fail($message)
    {
        $this->session->set_flashdata('error', $message); redirect('admin/members');
    }
}
