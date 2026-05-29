<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @property CI_Input $input
 * @property CI_Loader $load
 * @property CI_Session $session
 * @property Member_model $Member_model
 */
class Auth extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('Member_model');
	}

	public function index()
	{
		redirect('auth/login');
	}
	
	public function login()
	{
		$data = array(
			'error' => '',
			'success' => $this->session->flashdata('success'),
			'email' => ''
		);

		if ($this->input->method() === 'post')
		{
			$email = trim((string) $this->input->post('email', TRUE));
			$password = (string) $this->input->post('password', TRUE);

			$data['email'] = $email;

			if ($email === '' || $password === '')
			{
				$data['error'] = 'กรุณากรอกอีเมลและรหัสผ่าน';
			}
			else
			{
				$member = $this->Member_model->find_by_email($email);

				if (empty($member) || ! password_verify($password, $member['password_hash']))
				{
					$data['error'] = 'อีเมลหรือรหัสผ่านไม่ถูกต้อง';
				}
				elseif ((int) $member['status'] !== 1)
				{
					$data['error'] = 'บัญชีผู้ใช้นี้ถูกระงับการใช้งาน';
				}
				else
				{
					$this->session->set_userdata('member', array(
						'id' => (int) $member['id'],
						'full_name' => $member['title_name'].$member['first_name'].' '.$member['last_name'],
						'email' => $member['email']
					));
					redirect('');
					return;
				}
			}
		}

		$this->load->view('frontend/login', $data);
	}

	public function register()
	{
		$data = array(
			'error' => '',
			'success' => '',
			'title_name' => '',
			'first_name' => '',
			'last_name' => '',
			'position_name' => '',
			'organization_name' => '',
			'email' => '',
			'phone' => ''
		);

		if ($this->input->method() === 'post')
		{
			$title_name = trim((string) $this->input->post('title_name', TRUE));
			$first_name = trim((string) $this->input->post('first_name', TRUE));
			$last_name = trim((string) $this->input->post('last_name', TRUE));
			$position_name = trim((string) $this->input->post('position_name', TRUE));
			$organization_name = trim((string) $this->input->post('organization_name', TRUE));
			$email = trim((string) $this->input->post('email', TRUE));
			$phone = trim((string) $this->input->post('phone', TRUE));
			$password = (string) $this->input->post('password', TRUE);
			$confirm_password = (string) $this->input->post('confirm_password', TRUE);
			$accept_terms = (string) $this->input->post('accept_terms', TRUE);

			$data['title_name'] = $title_name;
			$data['first_name'] = $first_name;
			$data['last_name'] = $last_name;
			$data['position_name'] = $position_name;
			$data['organization_name'] = $organization_name;
			$data['email'] = $email;
			$data['phone'] = $phone;

			if ($title_name === '' || $first_name === '' || $last_name === '' || $position_name === '' || $organization_name === '' || $email === '' || $phone === '' || $password === '' || $confirm_password === '')
			{
				$data['error'] = 'กรุณากรอกข้อมูลให้ครบถ้วน';
			}
			elseif (! filter_var($email, FILTER_VALIDATE_EMAIL))
			{
				$data['error'] = 'รูปแบบอีเมลไม่ถูกต้อง';
			}
			elseif (strlen($password) < 8)
			{
				$data['error'] = 'รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร';
			}
			elseif ($password !== $confirm_password)
			{
				$data['error'] = 'รหัสผ่านและยืนยันรหัสผ่านไม่ตรงกัน';
			}
			elseif ($accept_terms !== '1')
			{
				$data['error'] = 'กรุณายืนยันความถูกต้องของข้อมูลและการยินยอม';
			}
			elseif ($this->Member_model->email_exists($email))
			{
				$data['error'] = 'อีเมลนี้ถูกสมัครใช้งานแล้ว';
			}
			else
			{
				$member_id = $this->Member_model->create(array(
					'title_name' => $title_name,
					'first_name' => $first_name,
					'last_name' => $last_name,
					'position_name' => $position_name,
					'organization_name' => $organization_name,
					'email' => $email,
					'phone' => $phone,
					'password' => $password
				));

				if ($member_id === FALSE)
				{
					$data['error'] = 'ไม่สามารถบันทึกข้อมูลสมัครสมาชิกได้ กรุณาลองใหม่อีกครั้ง';
				}
				else
				{
					$this->session->set_flashdata('success', 'สมัครสมาชิกสำเร็จ กรุณาเข้าสู่ระบบด้วยอีเมลและรหัสผ่านที่ลงทะเบียนไว้');
					redirect('auth/login');
					return;
				}
			}
		}

		$this->load->view('frontend/register', $data);
	}

	public function forgot_password()
	{
		$data = array(
			'error' => '',
			'success' => '',
			'email' => ''
		);

		if ($this->input->method() === 'post')
		{
			$email = trim((string) $this->input->post('email', TRUE));

			$data['email'] = $email;

			if ($email === '')
			{
				$data['error'] = 'กรุณากรอกอีเมลที่ใช้สมัครสมาชิก';
			}
			else
			{
				$data['success'] = 'ระบบรับคำขอรีเซ็ตรหัสผ่านแล้ว กรุณาตรวจสอบอีเมลของท่าน';
			}
		}

		$this->load->view('frontend/forgot_password', $data);
	}

	public function logout()
	{
		$this->session->unset_userdata('member');
		$this->session->set_flashdata('success', 'ออกจากระบบเรียบร้อยแล้ว');
		redirect(base_url());
	}
}
