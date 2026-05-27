<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Course_instructor_model extends CI_Model
{
    private $table = 'training_course_instructors';

    public function get_by_course($course_id)
    {
        return $this->db
            ->select('training_course_instructors.*, training_instructors.name AS instructor_name, training_instructors.position, training_instructors.department, training_instructors.email, training_instructors.phone, training_instructors.photo, training_instructors.is_active')
            ->from($this->table)
            ->join('training_instructors', 'training_instructors.id = training_course_instructors.instructor_id', 'left')
            ->where('training_course_instructors.course_id', (int) $course_id)
            ->order_by('training_course_instructors.sort_order', 'ASC')
            ->order_by('training_instructors.name', 'ASC')
            ->get()
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

    public function create_link($data)
    {
        $now = date('Y-m-d H:i:s');

        return $this->db->insert($this->table, array(
            'course_id' => (int) $data['course_id'],
            'instructor_id' => (int) $data['instructor_id'],
            'role' => $data['role'],
            'sort_order' => (int) $data['sort_order'],
            'created_at' => $now,
            'updated_at' => $now
        ));
    }

    public function update_link($id, $data)
    {
        return $this->db
            ->where('id', (int) $id)
            ->update($this->table, array(
                'instructor_id' => (int) $data['instructor_id'],
                'role' => $data['role'],
                'sort_order' => (int) $data['sort_order'],
                'updated_at' => date('Y-m-d H:i:s')
            ));
    }

    public function delete_link($id)
    {
        return $this->db
            ->where('id', (int) $id)
            ->delete($this->table);
    }

    public function link_exists($course_id, $instructor_id, $exclude_id = NULL)
    {
        $this->db
            ->where('course_id', (int) $course_id)
            ->where('instructor_id', (int) $instructor_id);

        if ($exclude_id !== NULL) {
            $this->db->where('id !=', (int) $exclude_id);
        }

        return $this->db->count_all_results($this->table) > 0;
    }

    public function get_stats($course_id)
    {
        return array(
            'total' => $this->db->where('course_id', (int) $course_id)->count_all_results($this->table),
            'main' => $this->db
                ->where('course_id', (int) $course_id)
                ->like('role', 'หลัก')
                ->count_all_results($this->table)
        );
    }
}
