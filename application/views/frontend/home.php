<?php $this->load->view('frontend/layouts/header'); ?>
<?php $this->load->view('frontend/layouts/topbar'); ?>
<?php $this->load->view('frontend/layouts/site_header'); ?>
<?php $this->load->view('frontend/layouts/nav'); ?>

<?php $this->load->view('frontend/home/hero'); ?>

	<main class="main">
		<?php $this->load->view('frontend/home/programs'); ?>
		<?php $this->load->view('frontend/home/info_band'); ?>
	</main>

<?php $this->load->view('frontend/layouts/footer'); ?>
