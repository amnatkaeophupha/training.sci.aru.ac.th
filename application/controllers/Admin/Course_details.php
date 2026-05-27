<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @property CI_Input $input
 * @property CI_Loader $load
 * @property CI_Session $session
 * @property Course_model $Course_model
 * @property Course_detail_model $Course_detail_model
 */
class Course_details extends CI_Controller
{
    private $section_types = array('learning', 'qualification', 'document', 'note');

    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->model('Course_model');
        $this->load->model('Course_detail_model');

        if (!$this->session->userdata('admin_logged_in')) {
            redirect('admin');
        }
    }

    public function index($course_id)
    {
        $course_id = (int) $course_id;
        $course = $this->Course_model->get_by_id($course_id);

        if (!$course) {
            $this->session->set_flashdata('error', 'ไม่พบข้อมูลหลักสูตร');
            redirect('admin/courses');
            return;
        }

        $edit_id = (int) $this->input->get('edit');
        $edit_detail = $edit_id > 0 ? $this->Course_detail_model->get_by_id($edit_id) : NULL;

        if ($edit_detail && (int) $edit_detail->course_id !== $course_id) {
            $this->session->set_flashdata('error', 'ข้อมูลรายละเอียดหลักสูตรไม่ถูกต้อง');
            redirect('admin/course-details/'.$course_id);
            return;
        }

        $this->load->view('admins/course_details', array(
            'course' => $course,
            'details' => $this->Course_detail_model->get_by_course($course_id),
            'stats' => $this->Course_detail_model->get_stats($course_id),
            'edit_detail' => $edit_detail,
            'section_types' => $this->section_types
        ));
    }

    public function store($course_id)
    {
        $course_id = (int) $course_id;

        if (!$this->Course_model->get_by_id($course_id)) {
            $this->session->set_flashdata('error', 'ไม่พบข้อมูลหลักสูตร');
            redirect('admin/courses');
            return;
        }

        $data = $this->get_post_data($course_id);
        $error = $this->validate_detail($data);

        if ($error !== '') {
            $this->session->set_flashdata('error', $error);
            redirect('admin/course-details/'.$course_id);
            return;
        }

        $this->Course_detail_model->create_detail($data);
        $this->session->set_flashdata('success', 'เพิ่มรายละเอียดหลักสูตรเรียบร้อยแล้ว');
        redirect('admin/course-details/'.$course_id);
    }

    public function update($id)
    {
        $detail = $this->Course_detail_model->get_by_id($id);

        if (!$detail) {
            $this->session->set_flashdata('error', 'ไม่พบข้อมูลรายละเอียดหลักสูตร');
            redirect('admin/courses');
            return;
        }

        $course_id = (int) $detail->course_id;
        $data = $this->get_post_data($course_id);
        $error = $this->validate_detail($data);

        if ($error !== '') {
            $this->session->set_flashdata('error', $error);
            redirect('admin/course-details/'.$course_id.'?edit='.(int) $id);
            return;
        }

        $this->Course_detail_model->update_detail($id, $data);
        $this->session->set_flashdata('success', 'แก้ไขรายละเอียดหลักสูตรเรียบร้อยแล้ว');
        redirect('admin/course-details/'.$course_id);
    }

    public function delete($id)
    {
        $detail = $this->Course_detail_model->get_by_id($id);

        if (!$detail) {
            $this->session->set_flashdata('error', 'ไม่พบข้อมูลรายละเอียดหลักสูตร');
            redirect('admin/courses');
            return;
        }

        $course_id = (int) $detail->course_id;
        $this->Course_detail_model->delete_detail($id);
        $this->session->set_flashdata('success', 'ลบรายละเอียดหลักสูตรเรียบร้อยแล้ว');
        redirect('admin/course-details/'.$course_id);
    }

    private function get_post_data($course_id)
    {
        return array(
            'course_id' => (int) $course_id,
            'section_type' => trim((string) $this->input->post('section_type', TRUE)),
            'title' => trim((string) $this->input->post('title', TRUE)),
            'content' => trim((string) $this->input->post('content', TRUE)),
            'sort_order' => (int) $this->input->post('sort_order', TRUE)
        );
    }

    private function validate_detail($data)
    {
        if (!in_array($data['section_type'], $this->section_types, TRUE)) {
            return 'ประเภทข้อมูลรายละเอียดหลักสูตรไม่ถูกต้อง';
        }

        if ($data['title'] === '' && $data['content'] === '') {
            return 'กรุณากรอกหัวข้อหรือรายละเอียดอย่างน้อยหนึ่งรายการ';
        }

        return '';
    }
}
