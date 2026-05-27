<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @property CI_Input $input
 * @property CI_Loader $load
 * @property CI_Session $session
 * @property Course_model $Course_model
 * @property Category_model $Category_model
 */
class Courses extends CI_Controller
{
    private $statuses = array(1, 2, 3);
    private $training_types = array('', 'online', 'onsite', 'hybrid');

    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->model('Course_model');
        $this->load->model('Category_model');

        if (!$this->session->userdata('admin_logged_in')) {
            redirect('admin');
        }
    }

    public function index()
    {
        $edit_id = (int) $this->input->get('edit');
        $edit_course = $edit_id > 0 ? $this->Course_model->get_by_id($edit_id) : NULL;

        $this->load->view('admins/courses', array(
            'courses' => $this->Course_model->get_all(),
            'categories' => $this->Category_model->get_all(),
            'stats' => $this->Course_model->get_stats(),
            'edit_course' => $edit_course,
            'statuses' => $this->statuses,
            'training_types' => $this->training_types
        ));
    }

    public function store()
    {
        $data = $this->get_post_data();
        $error = $this->validate_course($data);

        if ($error !== '') {
            $this->session->set_flashdata('error', $error);
            redirect('admin/courses');
            return;
        }

        $this->Course_model->create_course($data);
        $this->session->set_flashdata('success', 'เพิ่มหลักสูตรเรียบร้อยแล้ว');
        redirect('admin/courses');
    }

    public function update($id)
    {
        $course = $this->Course_model->get_by_id($id);

        if (!$course) {
            $this->session->set_flashdata('error', 'ไม่พบข้อมูลหลักสูตร');
            redirect('admin/courses');
            return;
        }

        $data = $this->get_post_data();
        $error = $this->validate_course($data, $id);

        if ($error !== '') {
            $this->session->set_flashdata('error', $error);
            redirect('admin/courses?edit='.(int) $id);
            return;
        }

        $this->Course_model->update_course($id, $data);
        $this->session->set_flashdata('success', 'แก้ไขหลักสูตรเรียบร้อยแล้ว');
        redirect('admin/courses');
    }

    public function delete($id)
    {
        $id = (int) $id;

        if (!$this->Course_model->get_by_id($id)) {
            $this->session->set_flashdata('error', 'ไม่พบข้อมูลหลักสูตร');
            redirect('admin/courses');
            return;
        }

        $this->Course_model->delete_course($id);
        $this->session->set_flashdata('success', 'ลบหลักสูตรเรียบร้อยแล้ว');
        redirect('admin/courses');
    }

    private function get_post_data()
    {
        $title = trim((string) $this->input->post('title', TRUE));
        $slug = trim((string) $this->input->post('slug', TRUE));
        $slug = $this->make_slug($slug === '' ? $title : $slug);

        return array(
            'category_id' => (int) $this->input->post('category_id', TRUE),
            'title' => $title,
            'slug' => $slug,
            'short_description' => trim((string) $this->input->post('short_description', TRUE)),
            'description' => trim((string) $this->input->post('description', TRUE)),
            'cover_image' => trim((string) $this->input->post('cover_image', TRUE)),
            'level' => trim((string) $this->input->post('level', TRUE)),
            'training_type' => (string) $this->input->post('training_type', TRUE),
            'location' => trim((string) $this->input->post('location', TRUE)),
            'duration_text' => trim((string) $this->input->post('duration_text', TRUE)),
            'capacity' => max(0, (int) $this->input->post('capacity', TRUE)),
            'fee' => max(0, (float) $this->input->post('fee', TRUE)),
            'status' => (int) $this->input->post('status', TRUE),
            'is_featured' => $this->input->post('is_featured', TRUE) === '1' ? 1 : 0,
            'published_at' => $this->normalize_datetime($this->input->post('published_at', TRUE))
        );
    }

    private function validate_course($data, $exclude_id = NULL)
    {
        if ($data['title'] === '' || $data['category_id'] <= 0) {
            return 'กรุณากรอกชื่อหลักสูตรและเลือกหมวดหมู่';
        }

        if (!$this->Category_model->get_by_id($data['category_id'])) {
            return 'หมวดหมู่หลักสูตรไม่ถูกต้อง';
        }

        if ($data['slug'] === '') {
            return 'กรุณากรอก slug หรือใช้ชื่อหลักสูตรที่สร้าง slug ได้';
        }

        if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $data['slug'])) {
            return 'slug ใช้ได้เฉพาะตัวอักษรภาษาอังกฤษตัวเล็ก ตัวเลข และเครื่องหมาย -';
        }

        if ($this->Course_model->slug_exists($data['slug'], $exclude_id)) {
            return 'slug นี้ถูกใช้งานแล้ว';
        }

        if (!in_array($data['status'], $this->statuses, TRUE)) {
            return 'สถานะหลักสูตรไม่ถูกต้อง';
        }

        if (!in_array($data['training_type'], $this->training_types, TRUE)) {
            return 'รูปแบบอบรมไม่ถูกต้อง';
        }

        return '';
    }

    private function make_slug($value)
    {
        $slug = strtolower(trim($value));
        $slug = preg_replace('/[^a-z0-9]+/i', '-', $slug);
        $slug = trim($slug, '-');

        return $slug !== '' ? $slug : 'course-'.date('YmdHis');
    }

    private function normalize_datetime($value)
    {
        $value = trim((string) $value);

        if ($value === '') {
            return NULL;
        }

        $timestamp = strtotime(str_replace('T', ' ', $value));

        return $timestamp ? date('Y-m-d H:i:s', $timestamp) : NULL;
    }
}
