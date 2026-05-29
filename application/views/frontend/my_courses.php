<?php
$member = isset($member) && is_array($member) ? $member : array();
$courses = isset($courses) && is_array($courses) ? $courses : array();
$is_demo = !empty($is_demo);
$success = isset($success) ? $success : '';
$error = isset($error) ? $error : '';
$full_name = trim((isset($member['title_name']) ? $member['title_name'] : '').(isset($member['first_name']) ? $member['first_name'] : '').' '.(isset($member['last_name']) ? $member['last_name'] : ''));
$avatar_text = $full_name !== ''
	? (function_exists('mb_substr') ? mb_substr($full_name, 0, 1, 'UTF-8') : substr($full_name, 0, 1))
	: 'M';

$type_labels = array(
	'online' => 'ออนไลน์',
	'onsite' => 'อบรมในพื้นที่',
	'hybrid' => 'Hybrid'
);

$registration_labels = array(
	1 => 'รออนุมัติ',
	2 => 'อนุมัติแล้ว',
	3 => 'ยกเลิก'
);

$registration_classes = array(
	1 => 'member-course__status--pending',
	2 => 'member-course__status--approved',
	3 => 'member-course__status--cancelled'
);

$registration_labels = array(
	1 => 'รอชำระเงิน / รออนุมัติ',
	2 => 'อนุมัติแล้ว',
	3 => 'ไม่อนุมัติ',
	4 => 'ยกเลิก',
	5 => 'เข้าอบรมแล้ว'
);

$registration_classes = array(
	1 => 'member-course__status--pending',
	2 => 'member-course__status--approved',
	3 => 'member-course__status--rejected',
	4 => 'member-course__status--cancelled',
	5 => 'member-course__status--attended'
);

$payment_labels = array(
	1 => 'รอชำระเงิน',
	2 => 'รอตรวจสอบสลิป',
	3 => 'ชำระเงินแล้ว',
	4 => 'ไม่ผ่านการตรวจสอบ'
);

$payment_classes = array(
	1 => 'member-course__payment-status--pending',
	2 => 'member-course__payment-status--checking',
	3 => 'member-course__payment-status--paid',
	4 => 'member-course__payment-status--rejected'
);

$payment_slip_status_labels = array(
	2 => 'รอตรวจสอบสลิป',
	3 => 'ชำระเงินแล้ว',
	4 => 'ไม่ผ่านการตรวจสอบ'
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

$format_registration_code = function ($code) {
	$code = trim((string) $code);

	if ($code === '') {
		return '-';
	}

	if (preg_match('/^SCI-(\d{4})(\d{2})(\d{2})\d{6}-(\d+)-(\d+)$/', $code, $matches)) {
		return 'SCI-'.substr($matches[1], -2).$matches[2].$matches[3].'-'.$matches[4].'-'.$matches[5];
	}

	return $code;
};

$format_money = function ($amount) {
	$amount = (float) $amount;
	return $amount > 0 ? number_format($amount, 2).' บาท' : 'ไม่มีค่าธรรมเนียม';
};

$this->load->view('frontend/layouts/header', array(
	'page_title' => 'หลักสูตรอบรมของฉัน | โปรแกรมการอบรม',
	'body_class' => 'page-profile'
));
$this->load->view('frontend/layouts/topbar');
$this->load->view('frontend/layouts/site_header');
$this->load->view('frontend/layouts/nav');
?>

	<main class="main profile-page">
		<div class="section profile-layout">
			<aside class="profile-sidebar">
				<div class="profile-user">
					<div class="profile-avatar"><?php echo html_escape($avatar_text); ?></div>
					<div>
						<strong><?php echo html_escape($full_name); ?></strong>
						<a href="<?php echo base_url('index.php/dashboard/profile'); ?>">แก้ไขข้อมูลส่วนตัว</a>
					</div>
				</div>

				<nav class="profile-menu" aria-label="เมนูบัญชีสมาชิก">
					<a href="<?php echo base_url('index.php/dashboard/profile'); ?>">บัญชีของฉัน</a>
					<a href="<?php echo base_url('index.php/dashboard/change_password'); ?>">เปลี่ยนรหัสผ่าน</a>
					<a class="is-active" href="<?php echo base_url('index.php/dashboard/courses'); ?>">หลักสูตรเข้าอบรม</a>
					<a href="#">ประวัติการอบรม</a>
					<a class="profile-menu__logout" href="<?php echo base_url('index.php/auth/logout'); ?>">ออกจากระบบ</a>
				</nav>
			</aside>

			<section class="profile-panel" aria-label="หลักสูตรอบรมของฉัน">
				<div class="profile-panel__head profile-panel__head--split">
					<div>
						<h2>หลักสูตรอบรมของฉัน</h2>
						<p>รายการหลักสูตรที่เลือกสมัครอบรม พร้อมสถานะและกำหนดการ</p>
					</div>
					<a class="course__link" href="<?php echo base_url('index.php#programs'); ?>">เลือกหลักสูตรเพิ่ม</a>
				</div>

				<?php if (!empty($success)): ?>
					<div class="login-alert login-alert--success" role="status"><?php echo html_escape($success); ?></div>
				<?php endif; ?>

				<?php if (!empty($error)): ?>
					<div class="login-alert" role="alert"><?php echo html_escape($error); ?></div>
				<?php endif; ?>

				<?php if ($is_demo): ?>
					<div class="member-course-note" role="status">
						ขณะนี้ยังไม่พบข้อมูลสมัครจริง จึงแสดงข้อมูลตัวอย่างเพื่อจัดวางหน้า
					</div>
				<?php endif; ?>

				<?php if (!empty($courses)): ?>
					<div class="member-course-list">
						<?php foreach ($courses as $course): ?>
							<?php
							$status = isset($course->registration_status) ? (int) $course->registration_status : 1;
							$status_label = isset($registration_labels[$status]) ? $registration_labels[$status] : 'รออนุมัติ';
							$status_class = isset($registration_classes[$status]) ? $registration_classes[$status] : 'member-course__status--pending';
							$training_type = !empty($course->training_type) && isset($type_labels[$course->training_type]) ? $type_labels[$course->training_type] : (isset($course->training_type) ? $course->training_type : '');
							$time_range = $format_time_range(isset($course->start_time) ? $course->start_time : '', isset($course->end_time) ? $course->end_time : '');
							$detail_url = !$is_demo && !empty($course->slug) ? base_url('index.php/home/detail/'.$course->slug) : base_url('index.php#programs');
							$participant_url = !$is_demo && !empty($course->registration_id) ? base_url('index.php/dashboard/participants/'.(int) $course->registration_id) : '#';
							$cover_image = !empty($course->cover_image) ? $course->cover_image : '';
							$cover_image_url = $cover_image !== '' && preg_match('#^https?://#i', $cover_image) ? $cover_image : ($cover_image !== '' ? base_url($cover_image) : '');
							$participant_count = isset($course->participant_count) ? max(1, (int) $course->participant_count) : 1;
							$capacity_number = isset($course->capacity) ? (int) $course->capacity : 0;
							$registered_count = isset($course->registered_count) ? (int) $course->registered_count : $participant_count;
							$is_course_full = $capacity_number > 0 && $registered_count >= $capacity_number;
							$fee_per_person = isset($course->fee_per_person) ? (float) $course->fee_per_person : (isset($course->fee) ? (float) $course->fee : 0);
							$payment_total = isset($course->payment_total) ? (float) $course->payment_total : ($fee_per_person * $participant_count);
							$payment_submitted_amount = isset($course->payment_submitted_amount) ? (float) $course->payment_submitted_amount : 0;
							$payment_due_amount = isset($course->payment_due_amount) ? (float) $course->payment_due_amount : $payment_total;
							$payment_refund_amount = isset($course->payment_refund_amount) ? (float) $course->payment_refund_amount : 0;
							$payment_status = isset($course->payment_status) ? (int) $course->payment_status : 0;
							$payment_label = isset($payment_labels[$payment_status]) ? $payment_labels[$payment_status] : ($payment_total > 0 ? 'รอชำระเงิน' : 'ไม่มีค่าธรรมเนียม');
							$payment_class = isset($payment_classes[$payment_status]) ? $payment_classes[$payment_status] : 'member-course__payment-status--pending';
							if ($payment_due_amount > 0 && $payment_submitted_amount > 0) {
								$payment_label = 'รอชำระเงินเพิ่มเติม';
							}
							if ($payment_refund_amount > 0) {
								$payment_label = 'ยอดชำระเกิน';
								$payment_class = 'member-course__payment-status--refund';
							}
							$payment_slip_url = !empty($course->payment_slip) ? base_url($course->payment_slip) : '';
							$payment_slips = isset($course->payment_slips) && is_array($course->payment_slips) ? $course->payment_slips : array();
							?>
							<article class="member-course">
								<div class="member-course__media">
									<?php if ($cover_image_url !== ''): ?>
										<img src="<?php echo html_escape($cover_image_url); ?>" alt="<?php echo html_escape($course->title); ?>" loading="lazy" decoding="async">
									<?php else: ?>
										<span><?php echo html_escape(function_exists('mb_substr') ? mb_substr($course->title, 0, 1, 'UTF-8') : substr($course->title, 0, 1)); ?></span>
									<?php endif; ?>
								</div>

								<div class="member-course__body">
									<div class="member-course__top">
										<div>
											<span class="member-course__eyebrow"><?php echo html_escape(!empty($course->category_name) ? $course->category_name : 'หลักสูตรอบรม'); ?></span>
											<h3><?php echo html_escape($course->title); ?></h3>
										</div>
										<span class="member-course__status <?php echo $status_class; ?>"><?php echo html_escape($status_label); ?></span>
									</div>

									<div class="member-course__meta">
										<span><?php echo html_escape($format_course_date($course->start_date, $course->end_date)); ?></span>
										<?php if ($time_range !== ''): ?>
											<span><?php echo html_escape($time_range); ?></span>
										<?php endif; ?>
										<?php if (!empty($course->batch_no)): ?>
											<span><?php echo html_escape($course->batch_no); ?></span>
										<?php endif; ?>
										<?php if (!empty($course->location)): ?>
											<span><?php echo html_escape($course->location); ?></span>
										<?php elseif ($training_type !== ''): ?>
											<span><?php echo html_escape($training_type); ?></span>
										<?php endif; ?>
									</div>

									<div class="member-course__payment">
										<div>
											<span>จำนวนผู้เข้าอบรม</span>
											<strong><?php echo number_format($participant_count); ?> ท่าน</strong>
										</div>
										<div>
											<span>ค่าลงทะเบียน / 1 ท่าน</span>
											<strong><?php echo html_escape($format_money($fee_per_person)); ?></strong>
										</div>
										<div class="member-course__payment-total">
											<span>ยอดที่ต้องชำระ</span>
											<strong><?php echo html_escape($format_money($payment_total)); ?></strong>
										</div>
										<?php if ($payment_submitted_amount > 0): ?>
											<div>
												<span>ยอดที่อัปโหลดสลิปแล้ว</span>
												<strong><?php echo html_escape($format_money($payment_submitted_amount)); ?></strong>
											</div>
										<?php endif; ?>
										<?php if ($payment_due_amount > 0 && $payment_submitted_amount > 0): ?>
											<div class="member-course__payment-total">
												<span>ยอดชำระเพิ่มเติม</span>
												<strong><?php echo html_escape($format_money($payment_due_amount)); ?></strong>
											</div>
										<?php endif; ?>
										<?php if ($payment_refund_amount > 0): ?>
											<div class="member-course__payment-refund">
												<span>ยอดที่ต้องรับคืน</span>
												<strong><?php echo html_escape($format_money($payment_refund_amount)); ?></strong>
												<em>กรุณาติดต่อรับเงินคืนในวัน เวลา ที่อบรม</em>
											</div>
										<?php endif; ?>
										<?php if ($payment_total > 0): ?>
											<div class="member-course__payment-upload">
												<div>
													<span>สถานะชำระเงิน</span>
													<strong class="member-course__payment-status <?php echo $payment_class; ?>"><?php echo html_escape($payment_label); ?></strong>
												</div>
												<?php if (!empty($payment_slips)): ?>
													<div class="member-course__slip-list">
														<span>รายการสลิปที่อัปโหลดแล้ว</span>
														<?php foreach ($payment_slips as $slip_index => $slip): ?>
															<?php
															$slip_status = isset($slip->status) ? (int) $slip->status : 0;
															$slip_label = isset($payment_slip_status_labels[$slip_status]) ? $payment_slip_status_labels[$slip_status] : 'อัปโหลดแล้ว';
															$slip_url = !empty($slip->payment_slip) ? base_url($slip->payment_slip) : '';
															?>
															<a href="<?php echo $slip_url; ?>" target="_blank" rel="noopener">
																<strong>สลิปครั้งที่ <?php echo number_format($slip_index + 1); ?></strong>
																<small><?php echo html_escape($format_money(isset($slip->amount) ? $slip->amount : 0)); ?> · <?php echo html_escape($slip_label); ?></small>
															</a>
														<?php endforeach; ?>
													</div>
												<?php endif; ?>
												<?php if ($payment_due_amount > 0): ?>
													<form method="post" action="<?php echo base_url('index.php/dashboard/payment-slip/'.(int) $course->registration_id); ?>" enctype="multipart/form-data">
														<input type="file" name="payment_slip" accept=".jpg,.jpeg,.png,.pdf" required>
														<button class="btn member-course__slip-btn" type="submit"><?php echo $payment_submitted_amount > 0 ? 'อัปโหลดสลิปเพิ่มเติม' : 'อัปโหลดสลิป'; ?></button>
													</form>
												<?php elseif (empty($payment_slips) && $payment_slip_url !== ''): ?>
													<a class="course__link" href="<?php echo $payment_slip_url; ?>" target="_blank" rel="noopener">ดูสลิปที่อัปโหลด</a>
												<?php endif; ?>
											</div>
										<?php endif; ?>
									</div>

									<div class="member-course__footer">
										<span>เลขที่สมัคร: <?php echo html_escape($format_registration_code(isset($course->registration_code) ? $course->registration_code : '')); ?></span>
										<div class="member-course__actions">
											<?php if ($is_course_full): ?>
												<span class="btn member-course__participant-btn member-course__participant-btn--full" aria-disabled="true">เต็ม</span>
											<?php else: ?>
												<a class="btn member-course__participant-btn" href="<?php echo $participant_url; ?>">เพิ่มผู้เข้าอบรม</a>
											<?php endif; ?>
											<a class="course__link" href="<?php echo $detail_url; ?>">ดูรายละเอียด</a>
										</div>
									</div>
								</div>
							</article>
						<?php endforeach; ?>
					</div>
				<?php else: ?>
					<div class="dashboard-empty">
						<strong>ยังไม่มีหลักสูตรอบรม</strong>
						<span>เลือกหลักสูตรที่สนใจจากหน้าแรก แล้วรายการจะมาแสดงที่นี่</span>
					</div>
				<?php endif; ?>
			</section>
		</div>
	</main>

	<div class="page-footer-spacer" aria-hidden="true"></div>

<?php $this->load->view('frontend/layouts/footer'); ?>
