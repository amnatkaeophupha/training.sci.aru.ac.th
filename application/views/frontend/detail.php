<?php
$course = isset($course) && is_object($course) ? $course : NULL;
$batches = isset($batches) && is_array($batches) ? $batches : array();
$course_details = isset($course_details) && is_array($course_details) ? $course_details : array();
$instructors = isset($instructors) && is_array($instructors) ? $instructors : array();
$documents = isset($documents) && is_array($documents) ? $documents : array();

if (!$course) {
	show_404();
	return;
}

$type_labels = array(
	'online' => 'ออนไลน์',
	'onsite' => 'อบรมในพื้นที่',
	'hybrid' => 'Hybrid'
);

$status_labels = array(
	1 => 'เปิดรับสมัคร',
	2 => 'ปิดรับสมัคร',
	3 => 'ยกเลิก'
);

$format_course_date = function ($start_date, $end_date = NULL) {
	if (empty($start_date)) {
		return 'รอประกาศ';
	}

	$months = array(
		1 => 'ม.ค.',
		2 => 'ก.พ.',
		3 => 'มี.ค.',
		4 => 'เม.ย.',
		5 => 'พ.ค.',
		6 => 'มิ.ย.',
		7 => 'ก.ค.',
		8 => 'ส.ค.',
		9 => 'ก.ย.',
		10 => 'ต.ค.',
		11 => 'พ.ย.',
		12 => 'ธ.ค.'
	);

	$start_time = strtotime($start_date);
	$end_time = !empty($end_date) ? strtotime($end_date) : $start_time;

	if (!$start_time) {
		return 'รอประกาศ';
	}

	$start_day = date('j', $start_time);
	$start_month = (int) date('n', $start_time);
	$start_year = (int) date('Y', $start_time) + 543;

	if ($end_time && date('Y-m-d', $start_time) !== date('Y-m-d', $end_time)) {
		$end_day = date('j', $end_time);
		$end_month = (int) date('n', $end_time);
		$end_year = (int) date('Y', $end_time) + 543;

		if ($start_month === $end_month && $start_year === $end_year) {
			return $start_day.'-'.$end_day.' '.$months[$start_month].' '.$start_year;
		}

		return $start_day.' '.$months[$start_month].' '.$start_year.' - '.$end_day.' '.$months[$end_month].' '.$end_year;
	}

	return $start_day.' '.$months[$start_month].' '.$start_year;
};

$details_by_type = array(
	'learning' => array(),
	'qualification' => array(),
	'document' => array(),
	'note' => array()
);

foreach ($course_details as $detail) {
	$type = isset($detail->section_type) ? $detail->section_type : '';

	if (!isset($details_by_type[$type])) {
		$details_by_type[$type] = array();
	}

	$details_by_type[$type][] = $detail;
}

$primary_batch = !empty($batches) ? $batches[0] : NULL;
$course_date = $primary_batch ? $format_course_date($primary_batch->start_date, $primary_batch->end_date) : 'รอประกาศ';
$training_type = isset($type_labels[$course->training_type]) ? $type_labels[$course->training_type] : $course->training_type;
$capacity = $primary_batch && !empty($primary_batch->capacity) ? (int) $primary_batch->capacity : (int) $course->capacity;
$fee_text = (float) $course->fee > 0 ? number_format((float) $course->fee).' บาท' : 'ไม่มีค่าใช้จ่าย';
$batch_status = $primary_batch && isset($status_labels[(int) $primary_batch->status]) ? $status_labels[(int) $primary_batch->status] : 'รอประกาศ';

$this->load->view('frontend/layouts/header', array(
	'page_title' => $course->title.' | คณะวิทยาศาสตร์และเทคโนโลยี',
	'body_class' => 'page-detail'
));
$this->load->view('frontend/layouts/topbar');
$this->load->view('frontend/layouts/site_header');
$this->load->view('frontend/layouts/nav');
?>

	<section class="hero">
		<div class="section hero__inner">
			<div>
				<div class="breadcrumb"><a href="<?php echo base_url('index.php'); ?>">หน้าหลัก</a> / <a href="<?php echo base_url('index.php#programs'); ?>">โปรแกรมอบรม</a> / รายละเอียด</div>
				<h2><?php echo html_escape($course->title); ?></h2>
				<p><?php echo html_escape(!empty($course->description) ? $course->description : $course->short_description); ?></p>
			</div>
			<div class="summary-card">
				<strong><?php echo html_escape(!empty($course->category_name) ? $course->category_name : 'หลักสูตรอบรม'); ?></strong>
				<span><?php echo html_escape($batch_status); ?></span>
				<p><?php echo html_escape($course_date); ?></p>
				<div class="actions">
					<a class="btn" href="#register">สมัครอบรม</a>
					<a class="btn btn--light" href="<?php echo base_url('index.php#programs'); ?>">กลับรายการ</a>
				</div>
			</div>
		</div>
	</section>

	<main class="main">
		<div class="section detail-grid">
			<div class="content">
				<section class="panel">
					<h3>ข้อมูลหลักสูตร</h3>
					<div class="info-grid">
						<div class="info-item">
							<span>วันอบรม</span>
							<strong><?php echo html_escape($course_date); ?></strong>
						</div>
						<div class="info-item">
							<span>ระยะเวลา</span>
							<strong><?php echo html_escape(!empty($course->duration_text) ? $course->duration_text : 'รอประกาศ'); ?></strong>
						</div>
						<div class="info-item">
							<span>รูปแบบ</span>
							<strong><?php echo html_escape(!empty($course->location) ? $course->location : $training_type); ?></strong>
						</div>
						<div class="info-item">
							<span>จำนวนรับ</span>
							<strong><?php echo $capacity > 0 ? 'รับ '.number_format($capacity).' คน' : 'รอประกาศ'; ?></strong>
						</div>
						<div class="info-item">
							<span>ค่าลงทะเบียน</span>
							<strong><?php echo html_escape($fee_text); ?></strong>
						</div>
						<div class="info-item">
							<span>ระดับหลักสูตร</span>
							<strong><?php echo html_escape(!empty($course->level) ? $course->level : 'ไม่ระบุ'); ?></strong>
						</div>
					</div>
				</section>

				<section class="panel">
					<h3>สิ่งที่จะได้เรียนรู้</h3>
					<?php if (!empty($details_by_type['learning'])): ?>
						<ul class="list">
							<?php foreach ($details_by_type['learning'] as $detail): ?>
								<li><strong><?php echo html_escape($detail->title); ?></strong> <?php echo html_escape($detail->content); ?></li>
							<?php endforeach; ?>
						</ul>
					<?php else: ?>
						<p><?php echo html_escape($course->short_description); ?></p>
					<?php endif; ?>
				</section>

				<section class="panel">
					<h3>กลุ่มเป้าหมาย</h3>
					<?php if (!empty($details_by_type['qualification'])): ?>
						<?php foreach ($details_by_type['qualification'] as $detail): ?>
							<p><?php echo html_escape($detail->content); ?></p>
						<?php endforeach; ?>
					<?php else: ?>
						<p>ผู้สนใจทั่วไป</p>
					<?php endif; ?>
				</section>

				<?php if (!empty($documents) || !empty($details_by_type['document'])): ?>
					<section class="panel">
						<h3>เอกสารประกอบ</h3>
						<ul class="list">
							<?php foreach ($details_by_type['document'] as $detail): ?>
								<li><strong><?php echo html_escape($detail->title); ?></strong> <?php echo html_escape($detail->content); ?></li>
							<?php endforeach; ?>
							<?php foreach ($documents as $document): ?>
								<li><a class="course__link" href="<?php echo base_url($document->file_path); ?>" target="_blank" rel="noopener"><?php echo html_escape($document->title); ?></a></li>
							<?php endforeach; ?>
						</ul>
					</section>
				<?php endif; ?>
			</div>

			<aside class="aside" id="register">
				<section class="panel register-box">
					<span class="status"><?php echo html_escape($batch_status); ?></span>
					<h3>สมัครเข้าร่วมอบรม</h3>
					<p>ตรวจสอบข้อมูลรอบอบรมและเตรียมข้อมูลผู้สมัครให้พร้อมก่อนส่งแบบฟอร์ม</p>
					<a class="btn" href="<?php echo base_url('index.php/auth/register'); ?>">กรอกใบสมัคร</a>
					<a class="btn btn--light" href="<?php echo base_url('index.php#programs'); ?>">ดูหลักสูตรอื่น</a>
				</section>

				<section class="panel">
					<h3>รอบอบรมที่เปิดรับสมัคร</h3>
					<?php if (!empty($batches)): ?>
						<ul class="list">
							<?php foreach ($batches as $batch): ?>
								<li>
									<strong><?php echo html_escape(!empty($batch->batch_no) ? $batch->batch_no : 'รอบอบรม'); ?></strong>
									<?php echo html_escape($format_course_date($batch->start_date, $batch->end_date)); ?>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php else: ?>
						<p>ยังไม่มีรอบอบรมที่เปิดรับสมัคร</p>
					<?php endif; ?>
				</section>

				<section class="panel">
					<h3>ผู้รับผิดชอบหลักสูตร</h3>
					<?php if (!empty($instructors)): ?>
						<?php foreach ($instructors as $instructor): ?>
							<?php if ((int) $instructor->is_active !== 1) { continue; } ?>
							<p>
								<strong><?php echo html_escape($instructor->instructor_name); ?></strong><br>
								<?php echo html_escape($instructor->role); ?>
							</p>
						<?php endforeach; ?>
					<?php else: ?>
						<p>ทีมวิทยากรคณะวิทยาศาสตร์และเทคโนโลยี</p>
					<?php endif; ?>
				</section>
			</aside>
		</div>
	</main>

<?php $this->load->view('frontend/layouts/footer'); ?>
