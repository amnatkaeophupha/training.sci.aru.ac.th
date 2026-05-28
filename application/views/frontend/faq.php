<?php
$faqs = array(
	array(
		'question' => 'สมัครอบรมได้อย่างไร',
		'answer' => 'เลือกหลักสูตรหรือรอบอบรมที่ต้องการ จากนั้นกดปุ่มสมัครอบรมในหน้ารายละเอียดหลักสูตร แล้วกรอกข้อมูลตามแบบฟอร์มให้ครบถ้วน'
	),
	array(
		'question' => 'ต้องมีบัญชีผู้ใช้ก่อนสมัครหรือไม่',
		'answer' => 'ผู้สมัครควรลงทะเบียนบัญชีผู้ใช้ก่อน เพื่อใช้กรอกใบสมัคร ติดตามสถานะ และจัดการข้อมูลการสมัครอบรม'
	),
	array(
		'question' => 'ตรวจสอบวันอบรมได้จากที่ไหน',
		'answer' => 'สามารถตรวจสอบวันอบรมได้จากหน้าโปรแกรมอบรม หน้าแสดงรายละเอียดหลักสูตร หรือเมนูปฏิทินอบรม'
	),
	array(
		'question' => 'ถ้าหลักสูตรปิดรับสมัครแล้วสามารถสมัครได้หรือไม่',
		'answer' => 'หากสถานะเป็นปิดรับสมัคร จะไม่สามารถสมัครผ่านระบบได้ โปรดติดตามรอบอบรมถัดไปหรือสอบถามข้อมูลเพิ่มเติมจากคณะฯ'
	),
	array(
		'question' => 'สถานะเปิดรับเพิ่มหมายถึงอะไร',
		'answer' => 'หมายถึงรอบอบรมนั้นเปิดรับผู้สมัครเพิ่มเติม ผู้สนใจยังสามารถกดสมัครอบรมได้ภายในช่วงเวลาที่กำหนด'
	),
	array(
		'question' => 'ติดต่อสอบถามข้อมูลหลักสูตรได้ที่ไหน',
		'answer' => 'ติดต่อคณะวิทยาศาสตร์และเทคโนโลยี โทร 0-3524-5888 หรืออีเมล science@aru.ac.th'
	)
);

$this->load->view('frontend/layouts/header', array(
	'page_title' => 'คำถามที่พบบ่อย | คณะวิทยาศาสตร์และเทคโนโลยี',
	'body_class' => 'page-faq'
));
$this->load->view('frontend/layouts/topbar');
$this->load->view('frontend/layouts/site_header');
$this->load->view('frontend/layouts/nav');
?>

	<section class="hero">
		<div class="section hero__inner">
			<div>
				<div class="breadcrumb"><a href="<?php echo base_url('index.php'); ?>">หน้าหลัก</a> / คำถามที่พบบ่อย</div>
				<h2>คำถามที่พบบ่อย</h2>
				<p class="hero__copy">รวบรวมคำตอบเกี่ยวกับการสมัครอบรม การตรวจสอบรอบอบรม และช่องทางติดต่อคณะฯ</p>
			</div>
			<div class="summary-card">
				<strong><?php echo number_format(count($faqs)); ?></strong>
				<span>คำถามสำคัญ</span>
				<p>สำหรับผู้สนใจเข้าร่วมอบรม</p>
				<div class="actions">
					<a class="btn" href="<?php echo base_url('index.php/home/contact'); ?>">ติดต่อสอบถาม</a>
				</div>
			</div>
		</div>
	</section>

	<main class="main">
		<section class="section faq-section">
			<div class="section__head">
				<div>
					<h3>ข้อมูลที่ควรรู้ก่อนสมัคร</h3>
					<p>เลือกอ่านคำถามที่ตรงกับเรื่องที่ต้องการ ระบบจะแสดงคำตอบโดยไม่ต้องออกจากหน้านี้</p>
				</div>
			</div>

			<div class="faq-list">
				<?php foreach ($faqs as $index => $faq): ?>
					<details class="faq-item" <?php echo $index === 0 ? 'open' : ''; ?>>
						<summary>
							<span><?php echo str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT); ?></span>
							<strong><?php echo html_escape($faq['question']); ?></strong>
						</summary>
						<div class="faq-item__body">
							<p><?php echo html_escape($faq['answer']); ?></p>
						</div>
					</details>
				<?php endforeach; ?>
			</div>
		</section>
	</main>

<?php $this->load->view('frontend/layouts/footer'); ?>
