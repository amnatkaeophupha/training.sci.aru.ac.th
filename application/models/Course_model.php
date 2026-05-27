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
