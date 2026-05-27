<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Category_model extends CI_Model
{
    private $table = 'training_categories';

    public function get_all()
    {
        return $this->db
            ->order_by('sort_order', 'ASC')
            ->order_by('id', 'DESC')
            ->get($this->table)
            ->result();
    }

    public function get_by_id($id)
    {
        return $this->db
            ->where('id', (int) $id)
            ->limit(1)
            ->get($this->table)
            ->row();
    }

    public function slug_exists($slug, $exclude_id = NULL)
    {
        $this->db->where('slug', $slug);

        if ($exclude_id !== NULL) {
            $this->db->where('id !=', (int) $exclude_id);
        }

        return $this->db->count_all_results($this->table) > 0;
    }

    public function create_category($data)
    {
        $now = date('Y-m-d H:i:s');

        return $this->db->insert($this->table, array(
            'name'        => $data['name'],
            'slug'        => $data['slug'],
            'description' => $data['description'],
            'sort_order'  => (int) $data['sort_order'],
            'is_active'   => (int) $data['is_active'],
            'created_at'  => $now,
            'updated_at'  => $now
        ));
    }

    public function update_category($id, $data)
    {
        return $this->db
            ->where('id', (int) $id)
            ->update($this->table, array(
                'name'        => $data['name'],
                'slug'        => $data['slug'],
                'description' => $data['description'],
                'sort_order'  => (int) $data['sort_order'],
                'is_active'   => (int) $data['is_active'],
                'updated_at'  => date('Y-m-d H:i:s')
            ));
    }

    public function delete_category($id)
    {
        return $this->db
            ->where('id', (int) $id)
            ->delete($this->table);
    }

    public function count_courses($id)
    {
        if (!$this->db->table_exists('training_courses')) {
            return 0;
        }

        return $this->db
            ->where('category_id', (int) $id)
            ->count_all_results('training_courses');
    }

    public function get_stats()
    {
        return array(
            'total' => $this->db->count_all($this->table),
            'active' => $this->db->where('is_active', 1)->count_all_results($this->table),
            'inactive' => $this->db->where('is_active', 0)->count_all_results($this->table)
        );
    }
}
