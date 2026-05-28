<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Course_model extends CI_Model
{
    private $table = 'training_courses';

    public function get_all()
    {
        return $this->db
            ->select('training_courses.*, training_categories.name AS category_name')
            ->from($this->table)
            ->join('training_categories', 'training_categories.id = training_courses.category_id', 'left')
            ->order_by('training_courses.id', 'DESC')
            ->get()
            ->result();
    }

    public function get_featured_for_frontend($limit = 6)
    {
        if (!$this->db->table_exists($this->table)) {
            return array();
        }

        $this->db
            ->select('training_courses.*')
            ->select('training_categories.name AS category_name')
            ->from($this->table)
            ->join('training_categories', 'training_categories.id = training_courses.category_id', 'left');

        if ($this->db->table_exists('training_batches')) {
            $this->db
                ->select('next_batch.id AS batch_id')
                ->select('next_batch.batch_no, next_batch.start_date, next_batch.end_date')
                ->select('next_batch.start_time, next_batch.end_time, next_batch.status AS batch_status')
                ->join(
                    'training_batches AS next_batch',
                    'next_batch.id = (
                        SELECT b.id
                        FROM training_batches AS b
                        WHERE b.course_id = training_courses.id
                            AND b.status IN (1, 3)
                        ORDER BY IFNULL(b.start_date, "9999-12-31") ASC, b.id ASC
                        LIMIT 1
                    )',
                    'left',
                    FALSE
                );
        }

        return $this->db
            ->where('training_courses.status', 2)
            ->where('training_courses.is_featured', 1)
            ->order_by('training_courses.published_at', 'DESC')
            ->order_by('training_courses.id', 'DESC')
            ->limit((int) $limit)
            ->get()
            ->result();
    }

    public function get_frontend_stats()
    {
        $stats = array(
            'open_courses' => 0,
            'active_categories' => 0,
            'open_batches' => 0,
            'registrations' => 0
        );

        if ($this->db->table_exists($this->table)) {
            $stats['open_courses'] = (int) $this->db
                ->where('status', 2)
                ->count_all_results($this->table);
        }

        if ($this->db->table_exists('training_categories')) {
            $stats['active_categories'] = (int) $this->db
                ->where('is_active', 1)
                ->count_all_results('training_categories');
        }

        if ($this->db->table_exists('training_batches')) {
            $stats['open_batches'] = (int) $this->db
                ->where_in('status', array(1, 3))
                ->count_all_results('training_batches');
        }

        if ($this->db->table_exists('training_registrations')) {
            $stats['registrations'] = (int) $this->db
                ->count_all_results('training_registrations');
        }

        return $stats;
    }

    public function get_by_id($id)
    {
        return $this->db
            ->where('id', (int) $id)
            ->limit(1)
            ->get($this->table)
            ->row();
    }

    public function get_frontend_by_slug($slug)
    {
        return $this->db
            ->select('training_courses.*, training_categories.name AS category_name')
            ->from($this->table)
            ->join('training_categories', 'training_categories.id = training_courses.category_id', 'left')
            ->where('training_courses.slug', (string) $slug)
            ->where('training_courses.status', 2)
            ->limit(1)
            ->get()
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

    public function create_course($data)
    {
        $now = date('Y-m-d H:i:s');

        return $this->db->insert($this->table, array(
            'category_id'       => (int) $data['category_id'],
            'title'             => $data['title'],
            'slug'              => $data['slug'],
            'short_description' => $data['short_description'],
            'description'       => $data['description'],
            'cover_image'       => $data['cover_image'],
            'level'             => $data['level'],
            'training_type'     => $data['training_type'],
            'location'          => $data['location'],
            'duration_text'     => $data['duration_text'],
            'capacity'          => (int) $data['capacity'],
            'fee'               => (float) $data['fee'],
            'status'            => (int) $data['status'],
            'is_featured'       => (int) $data['is_featured'],
            'published_at'      => $data['published_at'],
            'created_at'        => $now,
            'updated_at'        => $now
        ));
    }

    public function update_course($id, $data)
    {
        return $this->db
            ->where('id', (int) $id)
            ->update($this->table, array(
                'category_id'       => (int) $data['category_id'],
                'title'             => $data['title'],
                'slug'              => $data['slug'],
                'short_description' => $data['short_description'],
                'description'       => $data['description'],
                'cover_image'       => $data['cover_image'],
                'level'             => $data['level'],
                'training_type'     => $data['training_type'],
                'location'          => $data['location'],
                'duration_text'     => $data['duration_text'],
                'capacity'          => (int) $data['capacity'],
                'fee'               => (float) $data['fee'],
                'status'            => (int) $data['status'],
                'is_featured'       => (int) $data['is_featured'],
                'published_at'      => $data['published_at'],
                'updated_at'        => date('Y-m-d H:i:s')
            ));
    }

    public function delete_course($id)
    {
        return $this->db
            ->where('id', (int) $id)
            ->delete($this->table);
    }

    public function get_stats()
    {
        return array(
            'total' => $this->db->count_all($this->table),
            'draft' => $this->db->where('status', 1)->count_all_results($this->table),
            'open' => $this->db->where('status', 2)->count_all_results($this->table),
            'closed' => $this->db->where('status', 3)->count_all_results($this->table),
            'featured' => $this->db->where('is_featured', 1)->count_all_results($this->table)
        );
    }
}
