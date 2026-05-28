<?php
$batches = isset($batches) && is_array($batches) ? $batches : array();

$type_labels = array(
	'online' => 'ออนไลน์',
	'onsite' => 'อบรมในพื้นที่',
	'hybrid' => 'Hybrid'
);

$status_labels = array(
	1 => 'เปิดรับสมัคร',
	2 => 'ปิดรับสมัคร',
	3 => 'เปิดรับเพิ่ม',
	4 => 'ยกเลิก'
);

$status_classes = array(
	1 => 'calendar-status--open',
	2 => 'calendar-status--closed',
	3 => 'calendar-status--additional',
	4 => 'calendar-status--cancelled'
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

$format_time_range = function ($start_time, $end_time) {
	if (empty($start_time) && empty($end_time)) {
		return '';
	}

	$start = !empty($start_time) ? date('H:i', strtotime($start_time)) : '';
	$end = !empty($end_time) ? date('H:i', strtotime($end_time)) : '';

	return $start !== '' && $end !== '' ? $start.' - '.$end.' น.' : trim($start.$end).' น.';
};

$this->load->view('frontend/layouts/header', array(
	'page_title' => 'ปฏิทินอบรม | คณะวิทยาศาสตร์และเทคโนโลยี',
	'body_class' => 'page-calendar'
));
$this->load->view('frontend/layouts/topbar');
$this->load->view('frontend/layouts/site_header');
$this->load->view('frontend/layouts/nav');
?>

	<section class="hero">
		<div class="section hero__inner">
			<div>
				<div class="breadcrumb"><a href="<?php echo base_url('index.php'); ?>">หน้าหลัก</a> / ปฏิทินอบรม</div>
				<h2>ปฏิทินอบรม</h2>
				<p class="hero__copy">ติดตามรอบอบรมที่เปิดรับสมัคร กำหนดการอบรม และสถานะของแต่ละหลักสูตร</p>
			</div>
			<div class="summary-card">
				<!-- <strong><?php echo number_format(count($batches)); ?></strong> -->
				<span>รอบอบรมในปฏิทิน</span>
				<p>ข้อมูลจากตารางรอบอบรม</p>
				<div class="actions">
					<a class="btn" href="<?php echo base_url('index.php#programs'); ?>">ดูหลักสูตรหน้าแรก</a>
				</div>
			</div>
		</div>
	</section>

	<main class="main">
		<section class="section calendar-section">
			<div class="section__head">
				<div>
					<h3>กำหนดการอบรมทั้งหมด</h3>
					<p>เรียงตามสถานะและวันที่อบรม เพื่อให้ตรวจสอบรอบที่สมัครได้ก่อน</p>
				</div>
			</div>

			<?php if (!empty($batches)): ?>
				<div class="calendar-list">
					<?php foreach ($batches as $batch): ?>
						<?php
						$status = isset($batch->batch_status) ? (int) $batch->batch_status : 0;
						$status_label = isset($status_labels[$status]) ? $status_labels[$status] : 'รอประกาศ';
						$status_class = isset($status_classes[$status]) ? $status_classes[$status] : 'calendar-status--closed';
						$is_registration_open = in_array($status, array(1, 3), TRUE);
						$detail_url = !empty($batch->slug) ? base_url('index.php/home/detail/'.$batch->slug) : '#';
						$training_type = !empty($batch->training_type) && isset($type_labels[$batch->training_type]) ? $type_labels[$batch->training_type] : $batch->training_type;
						$capacity = !empty($batch->capacity) ? 'รับ '.number_format((int) $batch->capacity).' คน' : 'ไม่จำกัดจำนวน';
						$time_range = $format_time_range($batch->start_time, $batch->end_time);
						?>
						<article class="calendar-item">
							<div class="calendar-date">
								<span><?php echo html_escape($format_course_date($batch->start_date, $batch->end_date)); ?></span>
								<?php if ($time_range !== ''): ?>
									<strong><?php echo html_escape($time_range); ?></strong>
								<?php endif; ?>
							</div>
							<div class="calendar-content">
								<div class="calendar-title-row">
									<div>
										<h4><?php echo html_escape($batch->title); ?></h4>
										<p><?php echo html_escape(!empty($batch->short_description) ? $batch->short_description : $batch->description); ?></p>
									</div>
									<span class="calendar-status <?php echo $status_class; ?>"><?php echo html_escape($status_label); ?></span>
								</div>
								<div class="calendar-meta">
									<?php if (!empty($batch->batch_no)): ?>
										<span><?php echo html_escape($batch->batch_no); ?></span>
									<?php endif; ?>
									<?php if (!empty($batch->category_name)): ?>
										<span><?php echo html_escape($batch->category_name); ?></span>
									<?php endif; ?>
									<?php if (!empty($batch->location)): ?>
										<span><?php echo html_escape($batch->location); ?></span>
									<?php elseif (!empty($training_type)): ?>
										<span><?php echo html_escape($training_type); ?></span>
									<?php endif; ?>
									<span><?php echo html_escape($capacity); ?></span>
								</div>
								<div class="calendar-actions">
									<a class="course__link" href="<?php echo $detail_url; ?>">รายละเอียด</a>
									<?php if ($is_registration_open): ?>
										<a class="btn" href="<?php echo $detail_url; ?>#register">สมัครอบรม</a>
									<?php else: ?>
										<span class="calendar-closed" aria-disabled="true">ยังไม่เปิดให้สมัคร</span>
									<?php endif; ?>
								</div>
							</div>
						</article>
					<?php endforeach; ?>
				</div>
			<?php else: ?>
				<div class="dashboard-empty">
					<strong>ยังไม่มีรอบอบรมในปฏิทิน</strong>
					<span>เพิ่มข้อมูลรอบอบรมที่ระบบผู้ดูแล แล้วตั้งค่าสถานะให้เผยแพร่ในหน้าเว็บ</span>
				</div>
			<?php endif; ?>
		</section>
	</main>

<?php $this->load->view('frontend/layouts/footer'); ?>
