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

    public function get_frontend_programs($limit = 6)
    {
        if (!$this->db->table_exists($this->table) || !$this->db->table_exists('training_courses')) {
            return array();
        }

        return $this->db
            ->select('training_batches.id AS batch_id')
            ->select('training_batches.batch_no, training_batches.start_date, training_batches.end_date')
            ->select('training_batches.start_time, training_batches.end_time')
            ->select('training_batches.registration_start, training_batches.registration_end')
            ->select('training_batches.status AS batch_status')
            ->select('IF(training_batches.capacity > 0, training_batches.capacity, training_courses.capacity) AS capacity', FALSE)
            ->select('training_courses.id AS course_id')
            ->select('training_courses.title, training_courses.slug')
            ->select('training_courses.short_description, training_courses.description')
            ->select('training_courses.cover_image, training_courses.level')
            ->select('training_courses.training_type, training_courses.location, training_courses.duration_text')
            ->select('training_courses.fee, training_courses.status AS course_status')
            ->select('training_courses.is_featured, training_courses.published_at')
            ->select('training_categories.name AS category_name')
            ->from($this->table)
            ->join('training_courses', 'training_courses.id = training_batches.course_id', 'inner')
            ->join('training_categories', 'training_categories.id = training_courses.category_id', 'left')
            ->where('training_courses.status', 2)
            ->where_in('training_batches.status', array(1, 2, 3))
            ->order_by('FIELD(training_batches.status, 1, 3, 2)', '', FALSE)
            ->order_by('IFNULL(training_batches.start_date, "9999-12-31")', 'ASC', FALSE)
            ->order_by('training_batches.id', 'ASC')
            ->limit((int) $limit)
            ->get()
            ->result();
    }

    public function get_frontend_calendar()
    {
        if (!$this->db->table_exists($this->table) || !$this->db->table_exists('training_courses')) {
            return array();
        }

        return $this->db
            ->select('training_batches.id AS batch_id')
            ->select('training_batches.batch_no, training_batches.start_date, training_batches.end_date')
            ->select('training_batches.start_time, training_batches.end_time')
            ->select('training_batches.registration_start, training_batches.registration_end')
            ->select('training_batches.status AS batch_status')
            ->select('IF(training_batches.capacity > 0, training_batches.capacity, training_courses.capacity) AS capacity', FALSE)
            ->select('training_courses.id AS course_id')
            ->select('training_courses.title, training_courses.slug')
            ->select('training_courses.short_description, training_courses.description')
            ->select('training_courses.cover_image, training_courses.level')
            ->select('training_courses.training_type, training_courses.location, training_courses.duration_text')
            ->select('training_courses.fee, training_courses.status AS course_status')
            ->select('training_categories.name AS category_name')
            ->from($this->table)
            ->join('training_courses', 'training_courses.id = training_batches.course_id', 'inner')
            ->join('training_categories', 'training_categories.id = training_courses.category_id', 'left')
            ->where('training_courses.status', 2)
            ->where_in('training_batches.status', array(1, 2, 3, 4))
            ->order_by('FIELD(training_batches.status, 1, 3, 2, 4)', '', FALSE)
            ->order_by('IFNULL(training_batches.start_date, "9999-12-31")', 'ASC', FALSE)
            ->order_by('training_batches.id', 'ASC')
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
            ->where_in('status', array(1, 3))
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
            'additional_open' => $this->db->where('status', 3)->count_all_results($this->table),
            'cancelled' => $this->db->where('status', 4)->count_all_results($this->table)
        );
    }
}
