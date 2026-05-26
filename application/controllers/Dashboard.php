<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {

	public function index()
	{
		$member = $this->session->userdata('member');

		if (empty($member))
		{
			redirect('auth/login');
			return;
		}

		$this->load->view('frontend/dashboard', array(
			'member' => $member
		));
	}
}
