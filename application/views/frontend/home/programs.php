<?php
$featured_courses = isset($featured_courses) && is_array($featured_courses) ? $featured_courses : array();

$type_labels = array(
	'online' => 'ออนไลน์',
	'onsite' => 'อบรมในพื้นที่',
	'hybrid' => 'Hybrid'
);

$batch_status_labels = array(
	1 => 'เปิดรับสมัคร',
	2 => 'ปิดรับสมัคร',
	3 => 'เปิดรับเพิ่ม',
	4 => 'ยกเลิก'
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
?>
		<section class="section" id="programs">
			<?php $this->load->view('frontend/home/filterbar'); ?>

			<div class="section__head">
				<div>
					<h3>รายการหลักสูตรที่เปิดการอบรม</h3>
					<p>แสดงรายการจากตารางรอบอบรม พร้อมข้อมูลหลักสูตร วันอบรม ระยะเวลา รูปแบบ และสถานะการรับสมัคร</p>
				</div>
				<a class="course__link" href="<?php echo base_url('index.php/home/calendar'); ?>">ดูปฏิทินอบรมทั้งหมด</a>
			</div>

			<?php if (!empty($featured_courses)): ?>
				<div class="program-grid">
					<?php foreach ($featured_courses as $course): ?>
						<?php
						$category_name = !empty($course->category_name) ? $course->category_name : 'หลักสูตรอบรม';
						$description = !empty($course->short_description) ? $course->short_description : $course->description;
						$training_type = isset($type_labels[$course->training_type]) ? $type_labels[$course->training_type] : $course->training_type;
						$capacity = !empty($course->capacity) ? 'รับ '.$course->capacity.' คน' : '';
						$batch_status = isset($course->batch_status) && isset($batch_status_labels[(int) $course->batch_status]) ? $batch_status_labels[(int) $course->batch_status] : 'รอประกาศ';
						$is_registration_open = isset($course->batch_status) && in_array((int) $course->batch_status, array(1, 3), TRUE);
						$status_class = isset($course->batch_status) && (int) $course->batch_status === 3 ? 'course__status--additional' : 'course__status--open';
						$start_date = isset($course->start_date) ? $course->start_date : NULL;
						$end_date = isset($course->end_date) ? $course->end_date : NULL;
						$cover_image = !empty($course->cover_image) ? $course->cover_image : '';
						$cover_image_url = $cover_image !== '' && preg_match('#^https?://#i', $cover_image) ? $cover_image : base_url($cover_image);
						$detail_url = base_url('index.php/home/detail/'.$course->slug);
						?>
						<article class="course">
							<div class="course__media<?php echo $cover_image !== '' ? ' course__media--image' : ''; ?>">
								<?php if ($cover_image !== ''): ?>
									<img class="course__image" src="<?php echo html_escape($cover_image_url); ?>" alt="<?php echo html_escape($course->title); ?>" width="1140" height="420" loading="lazy" decoding="async" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;display:block;">
								<?php endif; ?>
								<span class="course__badge"><?php echo html_escape($category_name); ?></span>
							</div>
							<div class="course__body">
								<h4><?php echo html_escape($course->title); ?></h4>
								<p><?php echo html_escape($description); ?></p>
								<div class="meta">
									<?php if (!empty($course->duration_text)): ?>
										<span><?php echo html_escape($course->duration_text); ?></span>
									<?php endif; ?>
									<?php if (!empty($course->batch_no)): ?>
										<span><?php echo html_escape($course->batch_no); ?></span>
									<?php endif; ?>
									<?php if (!empty($course->location)): ?>
										<span><?php echo html_escape($course->location); ?></span>
									<?php elseif (!empty($training_type)): ?>
										<span><?php echo html_escape($training_type); ?></span>
									<?php endif; ?>
									<?php if (!empty($capacity)): ?>
										<span><?php echo html_escape($capacity); ?></span>
									<?php endif; ?>
								</div>
								<div class="course__footer">
									<span class="course__date"><?php echo html_escape($format_course_date($start_date, $end_date)); ?></span>
									<div class="course__actions">
										<a class="course__link" href="<?php echo $detail_url; ?>">รายละเอียด</a>
										<?php if ($is_registration_open): ?>
											<a class="course__status <?php echo $status_class; ?>" href="<?php echo $detail_url; ?>#register"><?php echo html_escape($batch_status); ?></a>
										<?php else: ?>
											<span class="course__status course__status--closed" aria-disabled="true"><?php echo html_escape($batch_status); ?></span>
										<?php endif; ?>
									</div>
								</div>
							</div>
						</article>
					<?php endforeach; ?>
				</div>
			<?php else: ?>
				<div class="dashboard-empty">
					<strong>ยังไม่มีหลักสูตรแนะนำ</strong>
					<span>เพิ่มข้อมูลหลักสูตรในระบบผู้ดูแล และตั้งค่าสถานะเป็น “เปิด” พร้อมเลือก “แสดงหน้าแรก”</span>
				</div>
			<?php endif; ?>
		</section>
