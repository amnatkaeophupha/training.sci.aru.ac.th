<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @property CI_Input $input
 * @property CI_Loader $load
 * @property CI_Session $session
 * @property Admin_model $Admin_model
 */
class Admin extends CI_Controller
{
    private $roles = array('super_admin', 'admin', 'staff', 'viewer');
    private $statuses = array('active', 'inactive');

    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->model('Admin_model');

        if (!$this->session->userdata('admin_logged_in')) {
            redirect('admin');
        }

        if ($this->session->userdata('admin_role') !== 'super_admin') {
            $this->session->set_flashdata('error', 'คุณไม่มีสิทธิ์จัดการผู้ดูแลระบบ');
            redirect('admin/dashboard');
        }
    }

    public function index()
    {
        $edit_id = (int) $this->input->get('edit');
        $edit_admin = $edit_id > 0 ? $this->Admin_model->get_by_id($edit_id) : NULL;

        $this->load->view('admins/admin', array(
            'admins' => $this->Admin_model->get_all(),
            'stats' => $this->Admin_model->get_stats(),
            'edit_admin' => $edit_admin,
            'roles' => $this->roles,
            'statuses' => $this->statuses
        ));
    }

    public function store()
    {
        $data = $this->get_post_data(TRUE);
        $error = $this->validate_admin($data, TRUE);

        if ($error !== '') {
            $this->session->set_flashdata('error', $error);
            redirect('admin/admins');
            return;
        }

        $this->Admin_model->create_admin($data);
        $this->session->set_flashdata('success', 'เพิ่มผู้ดูแลระบบเรียบร้อยแล้ว');
        redirect('admin/admins');
    }

    public function update($id)
    {
        $admin = $this->Admin_model->get_by_id($id);

        if (!$admin) {
            $this->session->set_flashdata('error', 'ไม่พบข้อมูลผู้ดูแลระบบ');
            redirect('admin/admins');
            return;
        }

        $data = $this->get_post_data(FALSE);
        $error = $this->validate_admin($data, FALSE, $id);

        if ($error !== '') {
            $this->session->set_flashdata('error', $error);
            redirect('admin/admins?edit='.(int) $id);
            return;
        }

        $this->Admin_model->update_admin($id, $data);
        $this->session->set_flashdata('success', 'แก้ไขข้อมูลผู้ดูแลระบบเรียบร้อยแล้ว');
        redirect('admin/admins');
    }

    public function delete($id)
    {
        $id = (int) $id;

        if ($id === (int) $this->session->userdata('admin_id')) {
            $this->session->set_flashdata('error', 'ไม่สามารถลบบัญชีที่กำลังใช้งานอยู่ได้');
            redirect('admin/admins');
            return;
        }

        if (!$this->Admin_model->get_by_id($id)) {
            $this->session->set_flashdata('error', 'ไม่พบข้อมูลผู้ดูแลระบบ');
            redirect('admin/admins');
            return;
        }

        $this->Admin_model->delete_admin($id);
        $this->session->set_flashdata('success', 'ลบผู้ดูแลระบบเรียบร้อยแล้ว');
        redirect('admin/admins');
    }

    private function get_post_data($password_required)
    {
        return array(
            'name' => trim((string) $this->input->post('name', TRUE)),
            'username' => trim((string) $this->input->post('username', TRUE)),
            'password' => (string) $this->input->post('password', TRUE),
            'role' => (string) $this->input->post('role', TRUE),
            'status' => (string) $this->input->post('status', TRUE),
            'password_required' => $password_required
        );
    }

    private function validate_admin($data, $password_required, $exclude_id = NULL)
    {
        if ($data['name'] === '' || $data['username'] === '' || $data['role'] === '' || $data['status'] === '') {
            return 'กรุณากรอกข้อมูลให้ครบถ้วน';
        }

        if (!in_array($data['role'], $this->roles, TRUE)) {
            return 'สิทธิ์ผู้ใช้งานไม่ถูกต้อง';
        }

        if (!in_array($data['status'], $this->statuses, TRUE)) {
            return 'สถานะผู้ใช้งานไม่ถูกต้อง';
        }

        if ($password_required && $data['password'] === '') {
            return 'กรุณากรอกรหัสผ่าน';
        }

        if ($data['password'] !== '' && strlen($data['password']) < 8) {
            return 'รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร';
        }

        if ($this->Admin_model->username_exists($data['username'], $exclude_id)) {
            return 'Username นี้ถูกใช้งานแล้ว';
        }

        return '';
    }
}
