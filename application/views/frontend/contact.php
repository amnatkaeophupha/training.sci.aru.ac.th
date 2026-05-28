<?php
$this->load->view('frontend/layouts/header', array(
	'page_title' => 'ติดต่อสอบถาม | คณะวิทยาศาสตร์และเทคโนโลยี',
	'body_class' => 'page-contact'
));
$this->load->view('frontend/layouts/topbar');
$this->load->view('frontend/layouts/site_header');
$this->load->view('frontend/layouts/nav');
?>

	<section class="hero">
		<div class="section hero__inner">
			<div>
				<div class="breadcrumb"><a href="<?php echo base_url('index.php'); ?>">หน้าหลัก</a> / ติดต่อสอบถาม</div>
				<h2>ติดต่อสอบถาม</h2>
				<p class="hero__copy">ช่องทางติดต่อคณะวิทยาศาสตร์และเทคโนโลยี สำหรับสอบถามข้อมูลหลักสูตรอบรมและการบริการวิชาการ</p>
			</div>
			<div class="summary-card">
				<strong>Science ARU</strong>
				<span>คณะวิทยาศาสตร์และเทคโนโลยี</span>
				<p>มหาวิทยาลัยราชภัฏพระนครศรีอยุธยา</p>
				<div class="actions">
					<a class="btn" href="mailto:science@aru.ac.th">ส่งอีเมล</a>
				</div>
			</div>
		</div>
	</section>

	<main class="main">
		<section class="section contact-section">
			<div class="section__head">
				<div>
					<h3>ข้อมูลติดต่อ</h3>
					<p>ติดต่อคณะฯ ได้ตามช่องทางด้านล่างในวันและเวลาราชการ</p>
				</div>
			</div>

			<div class="contact-grid">
				<article class="contact-card contact-card--address">
					<span class="contact-card__label">ที่อยู่</span>
					<h4>คณะวิทยาศาสตร์และเทคโนโลยี</h4>
					<p>
						มหาวิทยาลัยราชภัฏพระนครศรีอยุธยา<br>
						96 ถ.ปรีดีพนมยงค์ ต.ประตูชัย<br>
						อ.พระนครศรีอยุธยา<br>
						จ.พระนครศรีอยุธยา 13000
					</p>
				</article>

				<div class="contact-stack">
					<article class="contact-card">
						<span class="contact-card__label">โทรศัพท์</span>
						<a class="contact-card__link" href="tel:035245888">0-3524-5888</a>
					</article>

					<article class="contact-card">
						<span class="contact-card__label">อีเมล</span>
						<a class="contact-card__link" href="mailto:science@aru.ac.th">science@aru.ac.th</a>
					</article>
				</div>
			</div>
		</section>
	</main>

<?php $this->load->view('frontend/layouts/footer'); ?>
