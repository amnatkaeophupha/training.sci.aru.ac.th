<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @property CI_Input $input
 * @property CI_Loader $load
 * @property CI_Session $session
 * @property Category_model $Category_model
 */
class Categories extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->model('Category_model');

        if (!$this->session->userdata('admin_logged_in')) {
            redirect('admin');
        }
    }

    public function index()
    {
        $edit_id = (int) $this->input->get('edit');
        $edit_category = $edit_id > 0 ? $this->Category_model->get_by_id($edit_id) : NULL;

        $this->load->view('admins/categories', array(
            'categories' => $this->Category_model->get_all(),
            'stats' => $this->Category_model->get_stats(),
            'edit_category' => $edit_category
        ));
    }

    public function store()
    {
        $data = $this->get_post_data();
        $error = $this->validate_category($data);

        if ($error !== '') {
            $this->session->set_flashdata('error', $error);
            redirect('admin/categories');
            return;
        }

        $this->Category_model->create_category($data);
        $this->session->set_flashdata('success', 'เพิ่มหมวดหมู่หลักสูตรเรียบร้อยแล้ว');
        redirect('admin/categories');
    }

    public function update($id)
    {
        $category = $this->Category_model->get_by_id($id);

        if (!$category) {
            $this->session->set_flashdata('error', 'ไม่พบข้อมูลหมวดหมู่หลักสูตร');
            redirect('admin/categories');
            return;
        }

        $data = $this->get_post_data();
        $error = $this->validate_category($data, $id);

        if ($error !== '') {
            $this->session->set_flashdata('error', $error);
            redirect('admin/categories?edit='.(int) $id);
            return;
        }

        $this->Category_model->update_category($id, $data);
        $this->session->set_flashdata('success', 'แก้ไขหมวดหมู่หลักสูตรเรียบร้อยแล้ว');
        redirect('admin/categories');
    }

    public function delete($id)
    {
        $id = (int) $id;

        if (!$this->Category_model->get_by_id($id)) {
            $this->session->set_flashdata('error', 'ไม่พบข้อมูลหมวดหมู่หลักสูตร');
            redirect('admin/categories');
            return;
        }

        if ($this->Category_model->count_courses($id) > 0) {
            $this->session->set_flashdata('error', 'ไม่สามารถลบหมวดหมู่ที่มีหลักสูตรอยู่ได้');
            redirect('admin/categories');
            return;
        }

        $this->Category_model->delete_category($id);
        $this->session->set_flashdata('success', 'ลบหมวดหมู่หลักสูตรเรียบร้อยแล้ว');
        redirect('admin/categories');
    }

    private function get_post_data()
    {
        $name = trim((string) $this->input->post('name', TRUE));
        $slug = trim((string) $this->input->post('slug', TRUE));
        $slug = $this->make_slug($slug === '' ? $name : $slug);

        return array(
            'name' => $name,
            'slug' => $slug,
            'description' => trim((string) $this->input->post('description', TRUE)),
            'sort_order' => (int) $this->input->post('sort_order', TRUE),
            'is_active' => $this->input->post('is_active', TRUE) === '0' ? 0 : 1
        );
    }

    private function validate_category($data, $exclude_id = NULL)
    {
        if ($data['name'] === '') {
            return 'กรุณากรอกชื่อหมวดหมู่หลักสูตร';
        }

        if ($data['slug'] === '') {
            return 'กรุณากรอก slug หรือใช้ชื่อหมวดหมู่ที่สร้าง slug ได้';
        }

        if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $data['slug'])) {
            return 'slug ใช้ได้เฉพาะตัวอักษรภาษาอังกฤษตัวเล็ก ตัวเลข และเครื่องหมาย -';
        }

        if ($this->Category_model->slug_exists($data['slug'], $exclude_id)) {
            return 'slug นี้ถูกใช้งานแล้ว';
        }

        return '';
    }

    private function make_slug($value)
    {
        $slug = strtolower(trim($value));
        $slug = preg_replace('/[^a-z0-9]+/i', '-', $slug);
        $slug = trim($slug, '-');

        return $slug !== '' ? $slug : 'category-'.date('YmdHis');
    }
}
