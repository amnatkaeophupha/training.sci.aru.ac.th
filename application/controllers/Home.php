<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends CI_Controller {

	public function index()
	{
		
		//echo password_hash('123456', PASSWORD_DEFAULT);
		$this->load->view('frontend/home');
	}

	public function detail()
	{
		$this->load->view('frontend/detail');
	}
}
