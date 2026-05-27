<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @property CI_Input $input
 * @property CI_Loader $load
 * @property CI_Session $session
 * @property Course_model $Course_model
 * @property Instructor_model $Instructor_model
 * @property Course_instructor_model $Course_instructor_model
 */
class Course_instructors extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->model('Course_model');
        $this->load->model('Instructor_model');
        $this->load->model('Course_instructor_model');

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
        $edit_link = $edit_id > 0 ? $this->Course_instructor_model->get_by_id($edit_id) : NULL;

        if ($edit_link && (int) $edit_link->course_id !== $course_id) {
            $this->session->set_flashdata('error', 'ข้อมูลวิทยากรของหลักสูตรไม่ถูกต้อง');
            redirect('admin/course-instructors/'.$course_id);
            return;
        }

        $this->load->view('admins/course_instructors', array(
            'course' => $course,
            'course_instructors' => $this->Course_instructor_model->get_by_course($course_id),
            'instructors' => $this->Instructor_model->get_active(),
            'stats' => $this->Course_instructor_model->get_stats($course_id),
            'edit_link' => $edit_link
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
        $error = $this->validate_link($data);

        if ($error !== '') {
            $this->session->set_flashdata('error', $error);
            redirect('admin/course-instructors/'.$course_id);
            return;
        }

        $this->Course_instructor_model->create_link($data);
        $this->session->set_flashdata('success', 'เพิ่มวิทยากรให้หลักสูตรเรียบร้อยแล้ว');
        redirect('admin/course-instructors/'.$course_id);
    }

    public function update($id)
    {
        $id = (int) $id;
        $link = $this->Course_instructor_model->get_by_id($id);

        if (!$link) {
            $this->session->set_flashdata('error', 'ไม่พบข้อมูลวิทยากรของหลักสูตร');
            redirect('admin/courses');
            return;
        }

        $course_id = (int) $link->course_id;
        $data = $this->get_post_data($course_id);
        $error = $this->validate_link($data, $id);

        if ($error !== '') {
            $this->session->set_flashdata('error', $error);
            redirect('admin/course-instructors/'.$course_id.'?edit='.$id);
            return;
        }

        $this->Course_instructor_model->update_link($id, $data);
        $this->session->set_flashdata('success', 'แก้ไขวิทยากรของหลักสูตรเรียบร้อยแล้ว');
        redirect('admin/course-instructors/'.$course_id);
    }

    public function delete($id)
    {
        $id = (int) $id;
        $link = $this->Course_instructor_model->get_by_id($id);

        if (!$link) {
            $this->session->set_flashdata('error', 'ไม่พบข้อมูลวิทยากรของหลักสูตร');
            redirect('admin/courses');
            return;
        }

        $course_id = (int) $link->course_id;
        $this->Course_instructor_model->delete_link($id);
        $this->session->set_flashdata('success', 'ลบวิทยากรออกจากหลักสูตรเรียบร้อยแล้ว');
        redirect('admin/course-instructors/'.$course_id);
    }

    private function get_post_data($course_id)
    {
        return array(
            'course_id' => (int) $course_id,
            'instructor_id' => (int) $this->input->post('instructor_id', TRUE),
            'role' => trim((string) $this->input->post('role', TRUE)),
            'sort_order' => (int) $this->input->post('sort_order', TRUE)
        );
    }

    private function validate_link($data, $exclude_id = NULL)
    {
        if ($data['instructor_id'] <= 0) {
            return 'กรุณาเลือกวิทยากร';
        }

        $instructor = $this->Instructor_model->get_by_id($data['instructor_id']);

        if (!$instructor) {
            return 'ข้อมูลวิทยากรไม่ถูกต้อง';
        }

        if ((int) $instructor->is_active !== 1) {
            return 'วิทยากรนี้ถูกปิดใช้งานอยู่';
        }

        if ($this->Course_instructor_model->link_exists($data['course_id'], $data['instructor_id'], $exclude_id)) {
            return 'วิทยากรนี้ถูกเพิ่มในหลักสูตรแล้ว';
        }

        return '';
    }
}
