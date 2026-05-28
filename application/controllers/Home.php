<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends CI_Controller {

	/**
	 * @var Course_model
	 */
	public $Course_model;
	/**
	 * @var Batch_model
	 */
	public $Batch_model;
	/**
	 * @var Course_detail_model
	 */
	public $Course_detail_model;
	/**
	 * @var Course_instructor_model
	 */
	public $Course_instructor_model;
	/**
	 * @var Document_model
	 */
	public $Document_model;

	public function index()
	{
		//echo password_hash('123456', PASSWORD_DEFAULT);
		$this->load->model('Course_model');

		$this->load->view('frontend/home', array(
			'featured_courses' => $this->Course_model->get_featured_for_frontend(6),
			'hero_stats' => $this->Course_model->get_frontend_stats()
		));
	}

	public function detail($slug = NULL)
	{
		$this->load->model('Course_model');
		$this->load->model('Batch_model');
		$this->load->model('Course_detail_model');
		$this->load->model('Course_instructor_model');
		$this->load->model('Document_model');

		if ($slug === NULL || trim((string) $slug) === '') {
			show_404();
			return;
		}

		$course = $this->Course_model->get_frontend_by_slug($slug);

		if (!$course) {
			show_404();
			return;
		}

		$this->load->view('frontend/detail', array(
			'course' => $course,
			'batches' => $this->Batch_model->get_open_by_course($course->id),
			'course_details' => $this->Course_detail_model->get_by_course($course->id),
			'instructors' => $this->Course_instructor_model->get_by_course($course->id),
			'documents' => $this->Document_model->get_public_by_course($course->id)
		));
	}
}
