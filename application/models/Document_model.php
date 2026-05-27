<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Document_model extends CI_Model
{
    private $table = 'training_documents';

    public function get_all()
    {
        return $this->db
            ->select('training_documents.*, training_courses.title AS course_title, training_courses.slug AS course_slug')
            ->from($this->table)
            ->join('training_courses', 'training_courses.id = training_documents.course_id', 'left')
            ->order_by('training_courses.title', 'ASC')
            ->order_by('training_documents.sort_order', 'ASC')
            ->order_by('training_documents.id', 'DESC')
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

    public function create_document($data)
    {
        $now = date('Y-m-d H:i:s');

        return $this->db->insert($this->table, array(
            'course_id' => (int) $data['course_id'],
            'title' => $data['title'],
            'file_path' => $data['file_path'],
            'file_type' => $data['file_type'],
            'file_size' => (int) $data['file_size'],
            'sort_order' => (int) $data['sort_order'],
            'is_public' => (int) $data['is_public'],
            'created_at' => $now,
            'updated_at' => $now
        ));
    }

    public function update_document($id, $data)
    {
        return $this->db
            ->where('id', (int) $id)
            ->update($this->table, array(
                'course_id' => (int) $data['course_id'],
                'title' => $data['title'],
                'file_path' => $data['file_path'],
                'file_type' => $data['file_type'],
                'file_size' => (int) $data['file_size'],
                'sort_order' => (int) $data['sort_order'],
                'is_public' => (int) $data['is_public'],
                'updated_at' => date('Y-m-d H:i:s')
            ));
    }

    public function delete_document($id)
    {
        return $this->db
            ->where('id', (int) $id)
            ->delete($this->table);
    }

    public function get_stats()
    {
        return array(
            'total' => $this->db->count_all($this->table),
            'public' => $this->db->where('is_public', 1)->count_all_results($this->table),
            'private' => $this->db->where('is_public', 0)->count_all_results($this->table)
        );
    }
}
