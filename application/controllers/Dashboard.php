<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @property CI_Input $input
 * @property CI_Loader $load
 * @property CI_Session $session
 * @property CI_Upload $upload
 * @property Member_model $Member_model
 * @property Batch_model $Batch_model
 */
class Dashboard extends CI_Controller {

	private function require_member()
	{
		$member = $this->session->userdata('member');

		if (empty($member))
		{
			redirect('auth/login');
			return NULL;
		}

		return $member;
	}

	public function index()
	{
		$member = $this->require_member();

		if ($member === NULL) { return; }

		redirect('dashboard/profile');
	}

	public function profile()
	{
		$session_member = $this->require_member();

		if ($session_member === NULL) { return; }

		$this->load->model('Member_model');

		$member = $this->Member_model->find_by_id($session_member['id']);

		if (!$member)
		{
			$this->session->unset_userdata('member');
			redirect('auth/login');
			return;
		}

		$data = array(
			'error' => '',
			'success' => $this->session->flashdata('success'),
			'member' => $member
		);

		if ($this->input->method() === 'post')
		{
			$profile = array(
				'title_name' => trim((string) $this->input->post('title_name', TRUE)),
				'first_name' => trim((string) $this->input->post('first_name', TRUE)),
				'last_name' => trim((string) $this->input->post('last_name', TRUE)),
				'position_name' => trim((string) $this->input->post('position_name', TRUE)),
				'organization_name' => trim((string) $this->input->post('organization_name', TRUE)),
				'phone' => trim((string) $this->input->post('phone', TRUE))
			);

			if ($profile['title_name'] === '' || $profile['first_name'] === '' || $profile['last_name'] === '' || $profile['position_name'] === '' || $profile['organization_name'] === '' || $profile['phone'] === '')
			{
				$data['error'] = 'กรุณากรอกข้อมูลให้ครบถ้วน';
				$data['member'] = array_merge($member, $profile);
			}
			else
			{
				$this->Member_model->update_profile($member['id'], $profile);

				$full_name = $profile['title_name'].$profile['first_name'].' '.$profile['last_name'];
				$this->session->set_userdata('member', array(
					'id' => (int) $member['id'],
					'full_name' => $full_name,
					'email' => $member['email']
				));
				$this->session->set_flashdata('success', 'บันทึกข้อมูลส่วนตัวเรียบร้อยแล้ว');
				redirect('dashboard/profile');
				return;
			}
		}

		$this->load->view('frontend/profile', $data);
	}

	public function change_password()
	{
		$session_member = $this->require_member();

		if ($session_member === NULL) { return; }

		$this->load->model('Member_model');

		$member = $this->Member_model->find_by_id($session_member['id']);

		if (!$member)
		{
			$this->session->unset_userdata('member');
			redirect('auth/login');
			return;
		}

		$data = array(
			'error' => '',
			'success' => $this->session->flashdata('success'),
			'member' => $member
		);

		if ($this->input->method() === 'post')
		{
			$current_password = (string) $this->input->post('current_password', TRUE);
			$new_password = (string) $this->input->post('new_password', TRUE);
			$confirm_password = (string) $this->input->post('confirm_password', TRUE);

			if ($current_password === '' || $new_password === '' || $confirm_password === '')
			{
				$data['error'] = 'กรุณากรอกข้อมูลให้ครบถ้วน';
			}
			elseif (!$this->Member_model->verify_password($member['id'], $current_password))
			{
				$data['error'] = 'รหัสผ่านเดิมไม่ถูกต้อง';
			}
			elseif (strlen($new_password) < 8)
			{
				$data['error'] = 'รหัสผ่านใหม่ต้องมีอย่างน้อย 8 ตัวอักษร';
			}
			elseif ($new_password !== $confirm_password)
			{
				$data['error'] = 'ยืนยันรหัสผ่านใหม่ไม่ตรงกัน';
			}
			else
			{
				$this->Member_model->update_password($member['id'], $new_password);
				$this->session->set_flashdata('success', 'เปลี่ยนรหัสผ่านเรียบร้อยแล้ว');
				redirect('dashboard/change_password');
				return;
			}
		}

		$this->load->view('frontend/change_password', $data);
	}

	public function courses()
	{
		$session_member = $this->require_member();

		if ($session_member === NULL) { return; }

		$this->load->model('Member_model');
		$this->load->model('Batch_model');

		$member = $this->Member_model->find_by_id($session_member['id']);

		if (!$member)
		{
			$this->session->unset_userdata('member');
			redirect('auth/login');
			return;
		}

		$selected_batch_id = (int) $this->input->get('batch_id', TRUE);

		if ($selected_batch_id > 0)
		{
			$this->select_course_batch($member, $selected_batch_id);
			redirect('dashboard/courses');
			return;
		}

		$courses = $this->Batch_model->get_registered_by_member($member['id']);
		$session_courses = array();
		$selected_batch_ids = $this->session->userdata('selected_course_batches');

		if (!$this->Batch_model->registrations_table_exists() && !empty($selected_batch_ids))
		{
			$session_courses = $this->Batch_model->get_selected_batches($selected_batch_ids);
		}

		if (!empty($session_courses))
		{
			$registered_batch_ids = array();
			foreach ($courses as $course)
			{
				if (isset($course->batch_id))
				{
					$registered_batch_ids[] = (int) $course->batch_id;
				}
			}

			foreach ($session_courses as $course)
			{
				if (!isset($course->batch_id) || in_array((int) $course->batch_id, $registered_batch_ids, TRUE))
				{
					continue;
				}

				$courses[] = $course;
			}
		}

		$this->load->view('frontend/my_courses', array(
			'member' => $member,
			'courses' => $courses,
			'is_demo' => FALSE,
			'success' => $this->session->flashdata('success'),
			'error' => $this->session->flashdata('error')
		));
	}

	public function upload_payment_slip($registration_id = 0)
	{
		$session_member = $this->require_member();

		if ($session_member === NULL) { return; }

		$this->load->model('Member_model');
		$this->load->model('Batch_model');

		$member = $this->Member_model->find_by_id($session_member['id']);

		if (!$member)
		{
			$this->session->unset_userdata('member');
			redirect('auth/login');
			return;
		}

		$registration_id = (int) $registration_id;
		$registration = $this->Batch_model->get_registration_by_member($registration_id, $member['id']);

		if (!$registration)
		{
			$this->session->set_flashdata('error', 'ไม่พบรายการสมัครอบรมนี้');
			redirect('dashboard/courses');
			return;
		}

		if ((int) $registration->registration_status === 4)
		{
			$this->session->set_flashdata('error', 'รายการนี้ถูกยกเลิกแล้ว ไม่สามารถอัปโหลดสลิปได้');
			redirect('dashboard/courses');
			return;
		}

		if (!$this->Batch_model->payments_table_exists())
		{
			$this->session->set_flashdata('error', 'ยังไม่พบตารางการชำระเงิน กรุณารันไฟล์ Design/training_payments.sql ก่อน');
			redirect('dashboard/courses');
			return;
		}

		$upload_path = FCPATH.'uploads/payments/';

		if (!is_dir($upload_path) && !mkdir($upload_path, 0755, TRUE))
		{
			$this->session->set_flashdata('error', 'ไม่สามารถสร้างโฟลเดอร์อัปโหลดสลิปได้');
			redirect('dashboard/courses');
			return;
		}

		$config = array(
			'upload_path' => $upload_path,
			'allowed_types' => 'jpg|jpeg|png|pdf',
			'max_size' => 5120,
			'encrypt_name' => TRUE
		);

		$this->load->library('upload');
		$this->upload->initialize($config);

		if (!$this->upload->do_upload('payment_slip'))
		{
			$this->session->set_flashdata('error', strip_tags($this->upload->display_errors('', '')));
			redirect('dashboard/courses');
			return;
		}

		$upload_data = $this->upload->data();
		$slip_path = 'uploads/payments/'.$upload_data['file_name'];

		if (!$this->Batch_model->update_payment_slip($registration_id, $slip_path))
		{
			$this->session->set_flashdata('error', 'ไม่พบยอดที่ต้องอัปโหลดสลิปเพิ่มเติม');
			redirect('dashboard/courses');
			return;
		}
		$this->session->set_flashdata('success', 'อัปโหลดสลิปเรียบร้อยแล้ว กรุณารอเจ้าหน้าที่ตรวจสอบ');
		redirect('dashboard/courses');
	}

	public function cancel_registration($registration_id = 0)
	{
		$session_member = $this->require_member();

		if ($session_member === NULL) { return; }

		$this->load->model('Member_model');
		$this->load->model('Batch_model');

		$member = $this->Member_model->find_by_id($session_member['id']);

		if (!$member)
		{
			$this->session->unset_userdata('member');
			redirect('auth/login');
			return;
		}

		if ($this->input->method() !== 'post')
		{
			redirect('dashboard/courses');
			return;
		}

		$registration_id = (int) $registration_id;
		$registration = $this->Batch_model->get_registration_by_member($registration_id, $member['id']);

		if (!$registration)
		{
			$this->session->set_flashdata('error', 'ไม่พบรายการสมัครอบรมนี้');
			redirect('dashboard/courses');
			return;
		}

		if ((int) $registration->registration_status === 4)
		{
			$this->session->set_flashdata('error', 'รายการนี้ถูกยกเลิกแล้ว');
			redirect('dashboard/courses');
			return;
		}

		$this->Batch_model->cancel_registration_by_member($registration_id, $member['id']);
		$this->session->set_flashdata('success', 'ยกเลิกการสมัครเรียบร้อยแล้ว ระบบยังเก็บประวัติ รายชื่อผู้เข้าอบรม และรายการชำระเงินไว้ หากมีการชำระเงินแล้วกรุณาติดต่อเจ้าหน้าที่เพื่อดำเนินการคืนเงิน');
		redirect('dashboard/courses');
	}

	public function participants($registration_id = 0)
	{
		$session_member = $this->require_member();

		if ($session_member === NULL) { return; }

		$this->load->model('Member_model');
		$this->load->model('Batch_model');

		$member = $this->Member_model->find_by_id($session_member['id']);

		if (!$member)
		{
			$this->session->unset_userdata('member');
			redirect('auth/login');
			return;
		}

		$registration_id = (int) $registration_id;
		$registration = $this->Batch_model->get_registration_by_member($registration_id, $member['id']);

		if (!$registration)
		{
			$this->session->set_flashdata('error', 'ไม่พบรายการสมัครอบรมนี้');
			redirect('dashboard/courses');
			return;
		}

		if ((int) $registration->registration_status === 4)
		{
			$this->session->set_flashdata('error', 'รายการนี้ถูกยกเลิกแล้ว ไม่สามารถจัดการผู้เข้าอบรมได้');
			redirect('dashboard/courses');
			return;
		}

		if (!$this->Batch_model->participants_table_exists())
		{
			$this->session->set_flashdata('error', 'ยังไม่พบตารางผู้เข้าอบรม กรุณารันไฟล์ Design/training_registration_participants.sql ก่อน');
			redirect('dashboard/courses');
			return;
		}

		$error = '';
		$capacity_number = isset($registration->capacity) ? (int) $registration->capacity : 0;
		$registered_count = isset($registration->registered_count) ? (int) $registration->registered_count : 0;

		if ($this->input->method() === 'post')
		{
			$action = (string) $this->input->post('action', TRUE);

			if ($action === 'delete')
			{
				$participant_id = (int) $this->input->post('participant_id', TRUE);
				if ($participant_id > 0)
				{
					$this->Batch_model->delete_registration_participant($registration_id, $participant_id);
					$payment_summary = $this->Batch_model->sync_registration_payment($registration_id);
					$this->session->set_flashdata('success', 'ลบผู้เข้าอบรมเรียบร้อยแล้ว');
				}

				if (isset($payment_summary) && $payment_summary && (float) $payment_summary->refund_amount > 0)
				{
					$this->session->set_flashdata('success', 'ลบผู้เข้าอบรมเรียบร้อยแล้ว หากชำระเงินเกินยอดใหม่ กรุณาติดต่อรับเงินคืนในวัน เวลา ที่อบรม');
				}

				redirect('dashboard/participants/'.$registration_id);
				return;
			}

			$participant = array(
				'title_name' => trim((string) $this->input->post('title_name', TRUE)),
				'first_name' => trim((string) $this->input->post('first_name', TRUE)),
				'last_name' => trim((string) $this->input->post('last_name', TRUE)),
				'student_code' => trim((string) $this->input->post('student_code', TRUE)),
				'school_name' => trim((string) $this->input->post('school_name', TRUE)),
				'phone' => trim((string) $this->input->post('phone', TRUE)),
				'email' => trim((string) $this->input->post('email', TRUE)),
				'participant_type' => trim((string) $this->input->post('participant_type', TRUE)),
				'member_id' => 0,
				'is_main_member' => 0
			);

			if ($participant['participant_type'] === '')
			{
				$participant['participant_type'] = 'student';
			}

			if ($participant['first_name'] === '' || $participant['last_name'] === '')
			{
				$error = 'กรุณากรอกชื่อและนามสกุลผู้เข้าอบรม';
			}
			elseif ($participant['email'] !== '' && !filter_var($participant['email'], FILTER_VALIDATE_EMAIL))
			{
				$error = 'รูปแบบอีเมลไม่ถูกต้อง';
			}
			elseif ($action === 'update')
			{
				$participant_id = (int) $this->input->post('participant_id', TRUE);

				if ($participant_id <= 0 || !$this->Batch_model->update_registration_participant($registration_id, $participant_id, $participant))
				{
					$this->session->set_flashdata('error', 'ไม่สามารถแก้ไขข้อมูลผู้เข้าอบรมได้');
				}
				else
				{
					$this->session->set_flashdata('success', 'แก้ไขข้อมูลผู้เข้าอบรมเรียบร้อยแล้ว');
				}

				redirect('dashboard/participants/'.$registration_id);
				return;
			}
			else
			{
				if ($capacity_number > 0 && $registered_count >= $capacity_number)
				{
					$this->session->set_flashdata('error', 'จำนวนผู้เข้าอบรมเต็มแล้ว ไม่สามารถเพิ่มรายชื่อได้');
				}
				else
				{
					$this->Batch_model->create_registration_participant($registration_id, $participant);
					$payment_summary = $this->Batch_model->sync_registration_payment($registration_id);
					$this->session->set_flashdata('success', 'เพิ่มผู้เข้าอบรมเรียบร้อยแล้ว');
					if ($payment_summary && (float) $payment_summary->submitted_amount > 0 && (float) $payment_summary->due_amount > 0)
					{
						$this->session->set_flashdata('success', 'เพิ่มผู้เข้าอบรมเรียบร้อยแล้ว กรุณาอัปโหลดสลิปชำระเงินเพิ่มเติมในหน้าหลักสูตรของฉัน');
					}
				}
				redirect('dashboard/participants/'.$registration_id);
				return;
			}
		}

		$this->load->view('frontend/participants', array(
			'member' => $member,
			'registration' => $registration,
			'participants' => $this->Batch_model->get_registration_participants($registration_id),
			'success' => $this->session->flashdata('success'),
			'error' => $error !== '' ? $error : $this->session->flashdata('error')
		));
	}

	/**
	 * @param array $member
	 * @param int $batch_id
	 */
	private function select_course_batch($member, $batch_id)
	{
		$batch = $this->Batch_model->get_selectable_frontend_batch($batch_id);

		if (!$batch)
		{
			$this->session->set_flashdata('error', 'ไม่พบหลักสูตรที่เปิดรับสมัคร หรือหลักสูตรนี้ปิดรับสมัครแล้ว');
			return;
		}

		if ($this->Batch_model->registrations_table_exists())
		{
			$registration_id = $this->Batch_model->create_member_registration($member['id'], $batch_id);
			if ($registration_id)
			{
				$this->Batch_model->ensure_member_registration_participant($registration_id, $member);
			}
		}
		else
		{
			$selected_batch_ids = $this->session->userdata('selected_course_batches');
			$selected_batch_ids = is_array($selected_batch_ids) ? $selected_batch_ids : array();

			if (!in_array((int) $batch_id, $selected_batch_ids, TRUE))
			{
				$selected_batch_ids[] = (int) $batch_id;
				$this->session->set_userdata('selected_course_batches', $selected_batch_ids);
			}
		}

		$this->session->set_flashdata('success', 'เพิ่มหลักสูตรในรายการอบรมของฉันเรียบร้อยแล้ว');
	}

	private function get_demo_registered_courses()
	{
		return array(
			(object) array(
				'registration_code' => 'SCI-TRAIN-0001',
				'registration_status' => 2,
				'title' => 'การวิเคราะห์ข้อมูลด้วย Power BI สำหรับงานวิชาการ',
				'slug' => 'power-bi-academic',
				'category_name' => 'เทคโนโลยีสารสนเทศ',
				'batch_no' => 'รุ่นที่ 1/2569',
				'start_date' => '2026-06-12',
				'end_date' => '2026-06-13',
				'start_time' => '09:00:00',
				'end_time' => '16:00:00',
				'location' => 'ห้องปฏิบัติการคอมพิวเตอร์ คณะวิทยาศาสตร์และเทคโนโลยี',
				'training_type' => 'onsite',
				'cover_image' => '',
				'created_at' => '2026-05-20 10:30:00'
			),
			(object) array(
				'registration_code' => 'SCI-TRAIN-0002',
				'registration_status' => 1,
				'title' => 'การพัฒนาเว็บไซต์เบื้องต้นด้วย HTML CSS และ PHP',
				'slug' => 'web-development-basic',
				'category_name' => 'การพัฒนาซอฟต์แวร์',
				'batch_no' => 'รุ่นที่ 2/2569',
				'start_date' => '2026-07-04',
				'end_date' => '2026-07-05',
				'start_time' => '09:00:00',
				'end_time' => '16:30:00',
				'location' => 'Online ผ่านระบบ Zoom',
				'training_type' => 'online',
				'cover_image' => '',
				'created_at' => '2026-05-22 14:15:00'
			)
		);
	}
}
