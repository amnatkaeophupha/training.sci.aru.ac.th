<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Member_model extends CI_Model {

	private $table = 'members';

	public function __construct()
	{
		parent::__construct();
		$this->ensure_table();
	}

	private function ensure_table()
	{
		if ($this->db->table_exists($this->table))
		{
			return;
		}

		$this->load->dbforge();

		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => TRUE,
				'auto_increment' => TRUE
			),
			'title_name' => array(
				'type' => 'VARCHAR',
				'constraint' => 50
			),
			'first_name' => array(
				'type' => 'VARCHAR',
				'constraint' => 100
			),
			'last_name' => array(
				'type' => 'VARCHAR',
				'constraint' => 100
			),
			'position_name' => array(
				'type' => 'VARCHAR',
				'constraint' => 150
			),
			'organization_name' => array(
				'type' => 'VARCHAR',
				'constraint' => 200
			),
			'email' => array(
				'type' => 'VARCHAR',
				'constraint' => 190,
				'unique' => TRUE
			),
			'phone' => array(
				'type' => 'VARCHAR',
				'constraint' => 50
			),
			'password_hash' => array(
				'type' => 'VARCHAR',
				'constraint' => 255
			),
			'status' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'default' => 1
			),
			'created_at' => array(
				'type' => 'DATETIME',
				'null' => TRUE
			),
			'updated_at' => array(
				'type' => 'DATETIME',
				'null' => TRUE
			)
		));
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table($this->table, TRUE);
	}

	public function email_exists($email)
	{
		return $this->db
			->where('email', strtolower($email))
			->count_all_results($this->table) > 0;
	}

	public function create($data)
	{
		$now = date('Y-m-d H:i:s');

		$member = array(
			'title_name' => $data['title_name'],
			'first_name' => $data['first_name'],
			'last_name' => $data['last_name'],
			'position_name' => $data['position_name'],
			'organization_name' => $data['organization_name'],
			'email' => strtolower($data['email']),
			'phone' => $data['phone'],
			'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
			'status' => 1,
			'created_at' => $now,
			'updated_at' => $now
		);

		if ($this->db->insert($this->table, $member))
		{
			return $this->db->insert_id();
		}

		return FALSE;
	}

	public function find_by_email($email)
	{
		return $this->db
			->where('email', strtolower($email))
			->limit(1)
			->get($this->table)
			->row_array();
	}

	public function find_by_id($id)
	{
		return $this->db
			->where('id', (int) $id)
			->limit(1)
			->get($this->table)
			->row_array();
	}

	public function update_profile($id, $data)
	{
		return $this->db
			->where('id', (int) $id)
			->update($this->table, array(
				'title_name' => $data['title_name'],
				'first_name' => $data['first_name'],
				'last_name' => $data['last_name'],
				'position_name' => $data['position_name'],
				'organization_name' => $data['organization_name'],
				'phone' => $data['phone'],
				'updated_at' => date('Y-m-d H:i:s')
			));
	}
}
