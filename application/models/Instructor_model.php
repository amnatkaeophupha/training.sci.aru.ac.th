<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Instructor_model extends CI_Model
{
    private $table = 'training_instructors';

    public function get_all()
    {
        return $this->db
            ->order_by('is_active', 'DESC')
            ->order_by('name', 'ASC')
            ->order_by('id', 'DESC')
            ->get($this->table)
            ->result();
    }

    public function get_active()
    {
        return $this->db
            ->where('is_active', 1)
            ->order_by('name', 'ASC')
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

    public function create_instructor($data)
    {
        $now = date('Y-m-d H:i:s');

        return $this->db->insert($this->table, array(
            'name' => $data['name'],
            'position' => $data['position'],
            'department' => $data['department'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'photo' => $data['photo'],
            'bio' => $data['bio'],
            'is_active' => (int) $data['is_active'],
            'created_at' => $now,
            'updated_at' => $now
        ));
    }

    public function update_instructor($id, $data)
    {
        return $this->db
            ->where('id', (int) $id)
            ->update($this->table, array(
                'name' => $data['name'],
                'position' => $data['position'],
                'department' => $data['department'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'photo' => $data['photo'],
                'bio' => $data['bio'],
                'is_active' => (int) $data['is_active'],
                'updated_at' => date('Y-m-d H:i:s')
            ));
    }

    public function delete_instructor($id)
    {
        return $this->db
            ->where('id', (int) $id)
            ->delete($this->table);
    }

    public function count_course_links($id)
    {
        if (!$this->db->table_exists('training_course_instructors')) {
            return 0;
        }

        return $this->db
            ->where('instructor_id', (int) $id)
            ->count_all_results('training_course_instructors');
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
