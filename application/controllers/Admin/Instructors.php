<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @property CI_Input $input
 * @property CI_Loader $load
 * @property CI_Session $session
 * @property Instructor_model $Instructor_model
 */
class Instructors extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->model('Instructor_model');

        if (!$this->session->userdata('admin_logged_in')) {
            redirect('admin');
        }
    }

    public function index()
    {
        $edit_id = (int) $this->input->get('edit');
        $edit_instructor = $edit_id > 0 ? $this->Instructor_model->get_by_id($edit_id) : NULL;

        $this->load->view('admins/instructors', array(
            'instructors' => $this->Instructor_model->get_all(),
            'stats' => $this->Instructor_model->get_stats(),
            'edit_instructor' => $edit_instructor
        ));
    }

    public function store()
    {
        $data = $this->get_post_data();
        $error = $this->validate_instructor($data);

        if ($error !== '') {
            $this->session->set_flashdata('error', $error);
            redirect('admin/instructors');
            return;
        }

        $upload_error = $this->upload_photo($data);

        if ($upload_error !== '') {
            $this->session->set_flashdata('error', $upload_error);
            redirect('admin/instructors');
            return;
        }

        $this->Instructor_model->create_instructor($data);
        $this->session->set_flashdata('success', 'เพิ่มข้อมูลวิทยากรเรียบร้อยแล้ว');
        redirect('admin/instructors');
    }

    public function update($id)
    {
        $id = (int) $id;

        $instructor = $this->Instructor_model->get_by_id($id);

        if (!$instructor) {
            $this->session->set_flashdata('error', 'ไม่พบข้อมูลวิทยากร');
            redirect('admin/instructors');
            return;
        }

        $old_photo = (string) $instructor->photo;
        $data = $this->get_post_data($old_photo);
        $error = $this->validate_instructor($data);

        if ($error !== '') {
            $this->session->set_flashdata('error', $error);
            redirect('admin/instructors?edit='.$id);
            return;
        }

        $upload_error = $this->upload_photo($data);

        if ($upload_error !== '') {
            $this->session->set_flashdata('error', $upload_error);
            redirect('admin/instructors?edit='.$id);
            return;
        }

        $this->Instructor_model->update_instructor($id, $data);

        if ($data['photo'] !== $old_photo) {
            $this->delete_photo_file($old_photo);
        }

        $this->session->set_flashdata('success', 'แก้ไขข้อมูลวิทยากรเรียบร้อยแล้ว');
        redirect('admin/instructors');
    }

    public function delete($id)
    {
        $id = (int) $id;

        if (!$this->Instructor_model->get_by_id($id)) {
            $this->session->set_flashdata('error', 'ไม่พบข้อมูลวิทยากร');
            redirect('admin/instructors');
            return;
        }

        if ($this->Instructor_model->count_course_links($id) > 0) {
            $this->session->set_flashdata('error', 'ไม่สามารถลบวิทยากรที่ถูกเชื่อมกับหลักสูตรอยู่ได้');
            redirect('admin/instructors');
            return;
        }

        $this->Instructor_model->delete_instructor($id);
        $this->session->set_flashdata('success', 'ลบข้อมูลวิทยากรเรียบร้อยแล้ว');
        redirect('admin/instructors');
    }

    private function get_post_data($current_photo = '')
    {
        return array(
            'name' => trim((string) $this->input->post('name', TRUE)),
            'position' => trim((string) $this->input->post('position', TRUE)),
            'department' => trim((string) $this->input->post('department', TRUE)),
            'email' => trim((string) $this->input->post('email', TRUE)),
            'phone' => trim((string) $this->input->post('phone', TRUE)),
            'photo' => $current_photo,
            'bio' => trim((string) $this->input->post('bio', TRUE)),
            'is_active' => $this->input->post('is_active', TRUE) === '1' ? 1 : 0
        );
    }

    private function upload_photo(&$data)
    {
        if (empty($_FILES['photo']['name'])) {
            return '';
        }

        $upload_path = FCPATH.'uploads/instructors/';

        if (!is_dir($upload_path) && !mkdir($upload_path, 0755, TRUE)) {
            return 'ไม่สามารถสร้างโฟลเดอร์สำหรับอัปโหลดรูปภาพได้';
        }

        $config = array(
            'upload_path' => $upload_path,
            'allowed_types' => 'jpg|jpeg|png|gif|webp',
            'max_size' => 4096,
            'encrypt_name' => TRUE
        );

        $this->load->library('upload');
        $this->upload->initialize($config);

        if (!$this->upload->do_upload('photo')) {
            return strip_tags($this->upload->display_errors('', ''));
        }

        $upload_data = $this->upload->data();
        $data['photo'] = 'uploads/instructors/'.$upload_data['file_name'];

        return '';
    }

    private function delete_photo_file($photo)
    {
        $photo = trim((string) $photo);

        if ($photo === '' || strpos($photo, 'uploads/instructors/') !== 0) {
            return;
        }

        $path = FCPATH.$photo;

        if (is_file($path)) {
            @unlink($path);
        }
    }

    private function validate_instructor($data)
    {
        if ($data['name'] === '') {
            return 'กรุณากรอกชื่อวิทยากร';
        }

        if ($data['email'] !== '' && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return 'รูปแบบอีเมลไม่ถูกต้อง';
        }

        return '';
    }
}
