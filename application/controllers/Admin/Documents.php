<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @property CI_Input $input
 * @property CI_Loader $load
 * @property CI_Session $session
 * @property Document_model $Document_model
 * @property Course_model $Course_model
 */
class Documents extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->model('Document_model');
        $this->load->model('Course_model');

        if (!$this->session->userdata('admin_logged_in')) {
            redirect('admin');
        }
    }

    public function index()
    {
        $edit_id = (int) $this->input->get('edit');
        $edit_document = $edit_id > 0 ? $this->Document_model->get_by_id($edit_id) : NULL;

        $this->load->view('admins/documents', array(
            'documents' => $this->Document_model->get_all(),
            'courses' => $this->Course_model->get_all(),
            'stats' => $this->Document_model->get_stats(),
            'edit_document' => $edit_document
        ));
    }

    public function store()
    {
        $data = $this->get_post_data();
        $error = $this->validate_document($data, TRUE);

        if ($error !== '') {
            $this->session->set_flashdata('error', $error);
            redirect('admin/documents');
            return;
        }

        $upload_error = $this->upload_file($data);

        if ($upload_error !== '') {
            $this->session->set_flashdata('error', $upload_error);
            redirect('admin/documents');
            return;
        }

        $this->Document_model->create_document($data);
        $this->session->set_flashdata('success', 'เพิ่มเอกสารเรียบร้อยแล้ว');
        redirect('admin/documents');
    }

    public function update($id)
    {
        $id = (int) $id;
        $document = $this->Document_model->get_by_id($id);

        if (!$document) {
            $this->session->set_flashdata('error', 'ไม่พบข้อมูลเอกสาร');
            redirect('admin/documents');
            return;
        }

        $old_file = (string) $document->file_path;
        $data = $this->get_post_data($document);
        $error = $this->validate_document($data, FALSE);

        if ($error !== '') {
            $this->session->set_flashdata('error', $error);
            redirect('admin/documents?edit='.$id);
            return;
        }

        $upload_error = $this->upload_file($data);

        if ($upload_error !== '') {
            $this->session->set_flashdata('error', $upload_error);
            redirect('admin/documents?edit='.$id);
            return;
        }

        $this->Document_model->update_document($id, $data);

        if ($data['file_path'] !== $old_file) {
            $this->delete_document_file($old_file);
        }

        $this->session->set_flashdata('success', 'แก้ไขเอกสารเรียบร้อยแล้ว');
        redirect('admin/documents');
    }

    public function delete($id)
    {
        $id = (int) $id;
        $document = $this->Document_model->get_by_id($id);

        if (!$document) {
            $this->session->set_flashdata('error', 'ไม่พบข้อมูลเอกสาร');
            redirect('admin/documents');
            return;
        }

        $this->Document_model->delete_document($id);
        $this->delete_document_file($document->file_path);
        $this->session->set_flashdata('success', 'ลบเอกสารเรียบร้อยแล้ว');
        redirect('admin/documents');
    }

    private function get_post_data($current_document = NULL)
    {
        return array(
            'course_id' => (int) $this->input->post('course_id', TRUE),
            'title' => trim((string) $this->input->post('title', TRUE)),
            'file_path' => $current_document ? (string) $current_document->file_path : '',
            'file_type' => $current_document ? (string) $current_document->file_type : '',
            'file_size' => $current_document ? (int) $current_document->file_size : 0,
            'sort_order' => (int) $this->input->post('sort_order', TRUE),
            'is_public' => $this->input->post('is_public', TRUE) === '1' ? 1 : 0
        );
    }

    private function validate_document($data, $require_file)
    {
        if ($data['course_id'] <= 0) {
            return 'กรุณาเลือกหลักสูตร';
        }

        if (!$this->Course_model->get_by_id($data['course_id'])) {
            return 'หลักสูตรไม่ถูกต้อง';
        }

        if ($data['title'] === '') {
            return 'กรุณากรอกชื่อเอกสาร';
        }

        if ($require_file && empty($_FILES['document_file']['name'])) {
            return 'กรุณาเลือกไฟล์เอกสาร';
        }

        return '';
    }

    private function upload_file(&$data)
    {
        if (empty($_FILES['document_file']['name'])) {
            return '';
        }

        $upload_path = FCPATH.'uploads/documents/';

        if (!is_dir($upload_path) && !mkdir($upload_path, 0755, TRUE)) {
            return 'ไม่สามารถสร้างโฟลเดอร์สำหรับอัปโหลดเอกสารได้';
        }

        $config = array(
            'upload_path' => $upload_path,
            'allowed_types' => 'pdf|doc|docx|xls|xlsx|ppt|pptx|jpg|jpeg|png|gif|webp|zip|rar',
            'max_size' => 20480,
            'encrypt_name' => TRUE
        );

        $this->load->library('upload');
        $this->upload->initialize($config);

        if (!$this->upload->do_upload('document_file')) {
            return strip_tags($this->upload->display_errors('', ''));
        }

        $upload_data = $this->upload->data();
        $data['file_path'] = 'uploads/documents/'.$upload_data['file_name'];
        $data['file_type'] = strtolower(trim($upload_data['file_ext'], '.'));
        $data['file_size'] = (int) round((float) $upload_data['file_size'] * 1024);

        return '';
    }

    private function delete_document_file($file_path)
    {
        $file_path = trim((string) $file_path);

        if ($file_path === '' || strpos($file_path, 'uploads/documents/') !== 0) {
            return;
        }

        $path = FCPATH.$file_path;

        if (is_file($path)) {
            @unlink($path);
        }
    }
}
