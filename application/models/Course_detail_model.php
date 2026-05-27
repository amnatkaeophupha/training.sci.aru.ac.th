<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Course_detail_model extends CI_Model
{
    private $table = 'training_course_details';

    public function get_by_course($course_id)
    {
        return $this->db
            ->where('course_id', (int) $course_id)
            ->order_by('section_type', 'ASC')
            ->order_by('sort_order', 'ASC')
            ->order_by('id', 'ASC')
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

    public function create_detail($data)
    {
        $now = date('Y-m-d H:i:s');

        return $this->db->insert($this->table, array(
            'course_id' => (int) $data['course_id'],
            'section_type' => $data['section_type'],
            'title' => $data['title'],
            'content' => $data['content'],
            'sort_order' => (int) $data['sort_order'],
            'created_at' => $now,
            'updated_at' => $now
        ));
    }

    public function update_detail($id, $data)
    {
        return $this->db
            ->where('id', (int) $id)
            ->update($this->table, array(
                'section_type' => $data['section_type'],
                'title' => $data['title'],
                'content' => $data['content'],
                'sort_order' => (int) $data['sort_order'],
                'updated_at' => date('Y-m-d H:i:s')
            ));
    }

    public function delete_detail($id)
    {
        return $this->db
            ->where('id', (int) $id)
            ->delete($this->table);
    }

    public function get_stats($course_id)
    {
        $stats = array(
            'total' => 0,
            'learning' => 0,
            'qualification' => 0,
            'document' => 0,
            'note' => 0
        );

        $rows = $this->db
            ->select('section_type, COUNT(*) AS total')
            ->where('course_id', (int) $course_id)
            ->group_by('section_type')
            ->get($this->table)
            ->result();

        foreach ($rows as $row) {
            $type = (string) $row->section_type;
            $count = (int) $row->total;

            if (array_key_exists($type, $stats)) {
                $stats[$type] = $count;
            }

            $stats['total'] += $count;
        }

        return $stats;
    }
}
