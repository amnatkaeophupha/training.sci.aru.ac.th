<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_model extends CI_Model
{
    private $table = 'admins';

    public function get_all()
    {
        return $this->db
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

    public function get_by_username($username)
    {
        return $this->db
            ->where('username', $username)
            ->where('status', 'active')
            ->limit(1)
            ->get($this->table)
            ->row();
    }

    public function username_exists($username, $exclude_id = NULL)
    {
        $this->db->where('username', $username);

        if ($exclude_id !== NULL) {
            $this->db->where('id !=', (int) $exclude_id);
        }

        return $this->db->count_all_results($this->table) > 0;
    }

    public function create_admin($data)
    {
        $now = date('Y-m-d H:i:s');

        return $this->db->insert($this->table, array(
            'name'       => $data['name'],
            'username'   => $data['username'],
            'password'   => password_hash($data['password'], PASSWORD_DEFAULT),
            'role'       => $data['role'],
            'status'     => $data['status'],
            'created_at' => $now,
            'updated_at' => $now
        ));
    }

    public function update_admin($id, $data)
    {
        $admin = array(
            'name'       => $data['name'],
            'username'   => $data['username'],
            'role'       => $data['role'],
            'status'     => $data['status'],
            'updated_at' => date('Y-m-d H:i:s')
        );

        if (!empty($data['password'])) {
            $admin['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        return $this->db
            ->where('id', (int) $id)
            ->update($this->table, $admin);
    }

    public function delete_admin($id)
    {
        return $this->db
            ->where('id', (int) $id)
            ->delete($this->table);
    }

    public function get_stats()
    {
        return array(
            'total' => $this->db->count_all($this->table),
            'active' => $this->db->where('status', 'active')->count_all_results($this->table),
            'inactive' => $this->db->where('status', 'inactive')->count_all_results($this->table),
            'super_admin' => $this->db->where('role', 'super_admin')->count_all_results($this->table)
        );
    }
}
