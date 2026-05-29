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
			$this->ensure_password_reset_fields();
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
			'password_reset_token' => array(
				'type' => 'VARCHAR',
				'constraint' => 64,
				'null' => TRUE
			),
			'password_reset_expires_at' => array(
				'type' => 'DATETIME',
				'null' => TRUE
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

	private function ensure_password_reset_fields()
	{
		$this->load->dbforge();

		if (!$this->db->field_exists('password_reset_token', $this->table))
		{
			$this->dbforge->add_column($this->table, array(
				'password_reset_token' => array(
					'type' => 'VARCHAR',
					'constraint' => 64,
					'null' => TRUE,
					'after' => 'password_hash'
				)
			));
		}

		if (!$this->db->field_exists('password_reset_expires_at', $this->table))
		{
			$this->dbforge->add_column($this->table, array(
				'password_reset_expires_at' => array(
					'type' => 'DATETIME',
					'null' => TRUE,
					'after' => 'password_reset_token'
				)
			));
		}
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

	public function verify_password($id, $password)
	{
		$member = $this->find_by_id($id);

		if (!$member || empty($member['password_hash']))
		{
			return FALSE;
		}

		return password_verify($password, $member['password_hash']);
	}

	public function update_password($id, $password)
	{
		return $this->db
			->where('id', (int) $id)
			->update($this->table, array(
				'password_hash' => password_hash($password, PASSWORD_DEFAULT),
				'password_reset_token' => NULL,
				'password_reset_expires_at' => NULL,
				'updated_at' => date('Y-m-d H:i:s')
			));
	}

	public function create_password_reset_token($email)
	{
		$member = $this->find_by_email($email);

		if (!$member || (int) $member['status'] !== 1)
		{
			return '';
		}

		$token = bin2hex(random_bytes(32));
		$this->db
			->where('id', (int) $member['id'])
			->update($this->table, array(
				'password_reset_token' => hash('sha256', $token),
				'password_reset_expires_at' => date('Y-m-d H:i:s', strtotime('+1 hour')),
				'updated_at' => date('Y-m-d H:i:s')
			));

		return $token;
	}

	public function find_by_password_reset_token($token)
	{
		$token = trim((string) $token);

		if ($token === '')
		{
			return NULL;
		}

		return $this->db
			->where('password_reset_token', hash('sha256', $token))
			->where('password_reset_expires_at >=', date('Y-m-d H:i:s'))
			->where('status', 1)
			->limit(1)
			->get($this->table)
			->row_array();
	}
}
