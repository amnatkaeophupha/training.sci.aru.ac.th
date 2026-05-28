<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Batch_model extends CI_Model
{
    private $table = 'training_batches';

    public function get_all()
    {
        return $this->db
            ->select('training_batches.*, training_courses.title AS course_title, training_categories.name AS category_name')
            ->from($this->table)
            ->join('training_courses', 'training_courses.id = training_batches.course_id', 'left')
            ->join('training_categories', 'training_categories.id = training_courses.category_id', 'left')
            ->order_by('training_batches.start_date', 'DESC')
            ->order_by('training_batches.id', 'DESC')
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

    public function get_open_by_course($course_id)
    {
        return $this->db
            ->where('course_id', (int) $course_id)
            ->where('status', 1)
            ->order_by('start_date', 'ASC')
            ->order_by('id', 'ASC')
            ->get($this->table)
            ->result();
    }

    public function create_batch($data)
    {
        $now = date('Y-m-d H:i:s');

        return $this->db->insert($this->table, array(
            'course_id'           => (int) $data['course_id'],
            'batch_no'            => $data['batch_no'],
            'start_date'          => $data['start_date'],
            'end_date'            => $data['end_date'],
            'start_time'          => $data['start_time'],
            'end_time'            => $data['end_time'],
            'registration_start'  => $data['registration_start'],
            'registration_end'    => $data['registration_end'],
            'capacity'            => (int) $data['capacity'],
            'status'              => (int) $data['status'],
            'created_at'          => $now,
            'updated_at'          => $now
        ));
    }

    public function update_batch($id, $data)
    {
        return $this->db
            ->where('id', (int) $id)
            ->update($this->table, array(
                'course_id'           => (int) $data['course_id'],
                'batch_no'            => $data['batch_no'],
                'start_date'          => $data['start_date'],
                'end_date'            => $data['end_date'],
                'start_time'          => $data['start_time'],
                'end_time'            => $data['end_time'],
                'registration_start'  => $data['registration_start'],
                'registration_end'    => $data['registration_end'],
                'capacity'            => (int) $data['capacity'],
                'status'              => (int) $data['status'],
                'updated_at'          => date('Y-m-d H:i:s')
            ));
    }

    public function delete_batch($id)
    {
        return $this->db
            ->where('id', (int) $id)
            ->delete($this->table);
    }

    public function count_registrations($id)
    {
        if (!$this->db->table_exists('training_registrations')) {
            return 0;
        }

        return $this->db
            ->where('batch_id', (int) $id)
            ->count_all_results('training_registrations');
    }

    public function get_stats()
    {
        return array(
            'total' => $this->db->count_all($this->table),
            'open' => $this->db->where('status', 1)->count_all_results($this->table),
            'closed' => $this->db->where('status', 2)->count_all_results($this->table),
            'cancelled' => $this->db->where('status', 3)->count_all_results($this->table)
        );
    }
}
