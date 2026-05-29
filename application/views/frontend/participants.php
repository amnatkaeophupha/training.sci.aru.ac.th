<?php
$member = isset($member) && is_array($member) ? $member : array();
$registration = isset($registration) && is_object($registration) ? $registration : (object) array();
$registration_defaults = array(
	'registration_id' => 0,
	'registration_code' => '',
	'category_name' => '',
	'title' => '',
	'start_date' => '',
	'end_date' => '',
	'start_time' => '',
	'end_time' => '',
	'batch_no' => ''
);
foreach ($registration_defaults as $field => $value) {
	if (!isset($registration->{$field})) {
		$registration->{$field} = $value;
	}
}
$participants = isset($participants) && is_array($participants) ? $participants : array();
$success = isset($success) ? $success : '';
$error = isset($error) ? $error : '';
$capacity_number = isset($registration->capacity) ? (int) $registration->capacity : 0;
$registered_count = isset($registration->registered_count) ? (int) $registration->registered_count : count($participants);
$is_full = $capacity_number > 0 && $registered_count >= $capacity_number;
$remaining_count = $capacity_number > 0 ? max(0, $capacity_number - $registered_count) : 0;
$full_name = trim((isset($member['title_name']) ? $member['title_name'] : '').(isset($member['first_name']) ? $member['first_name'] : '').' '.(isset($member['last_name']) ? $member['last_name'] : ''));
$avatar_text = $full_name !== ''
	? (function_exists('mb_substr') ? mb_substr($full_name, 0, 1, 'UTF-8') : substr($full_name, 0, 1))
	: 'M';

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
	'page_title' => 'เพิ่มผู้เข้าอบรม | โปรแกรมการอบรม',
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
					<!-- <a href="<?php echo base_url('index.php/home/calendar'); ?>">ปฏิทินอบรม</a> -->
					<a class="profile-menu__logout" href="<?php echo base_url('index.php/auth/logout'); ?>">ออกจากระบบ</a>
				</nav>
			</aside>

			<section class="profile-panel" aria-label="เพิ่มผู้เข้าอบรม">
				<div class="profile-panel__head profile-panel__head--split">
					<div>
						<h2>เพิ่มผู้เข้าอบรม</h2>
						<p>จัดการรายชื่อผู้เข้าร่วมอบรมสำหรับรายการสมัครนี้</p>
					</div>
					<a class="course__link" href="<?php echo base_url('index.php/dashboard/courses'); ?>">กลับหลักสูตรของฉัน</a>
				</div>

				<?php if (!empty($success)): ?>
					<div class="login-alert login-alert--success" role="status"><?php echo html_escape($success); ?></div>
				<?php endif; ?>

				<?php if (!empty($error)): ?>
					<div class="login-alert" role="alert"><?php echo html_escape($error); ?></div>
				<?php endif; ?>

				<div class="participant-summary">
					<div>
						<span><?php echo html_escape(!empty($registration->category_name) ? $registration->category_name : 'หลักสูตรอบรม'); ?></span>
						<h3><?php echo html_escape($registration->title); ?></h3>
					</div>
					<div class="participant-summary__meta">
						<span>เลขที่สมัคร: <?php echo html_escape($format_registration_code($registration->registration_code)); ?></span>
						<span><?php echo html_escape($format_course_date($registration->start_date, $registration->end_date)); ?></span>
						<?php $time_range = $format_time_range($registration->start_time, $registration->end_time); ?>
						<?php if ($time_range !== ''): ?>
							<span><?php echo html_escape($time_range); ?></span>
						<?php endif; ?>
						<?php if (!empty($registration->batch_no)): ?>
							<span><?php echo html_escape($registration->batch_no); ?></span>
						<?php endif; ?>
						<?php if ($capacity_number > 0): ?>
							<span>รับ <?php echo number_format($capacity_number); ?> คน</span>
							<span>คงเหลือ <?php echo number_format($remaining_count); ?> คน</span>
						<?php endif; ?>
					</div>
				</div>

				<div class="participant-toolbar">
					<?php if ($is_full): ?>
						<span class="btn member-course__participant-btn member-course__participant-btn--full" aria-disabled="true">เต็ม</span>
					<?php else: ?>
						<button class="btn" type="button" data-bs-toggle="modal" data-bs-target="#participantFormModal">เพิ่มผู้เข้าอบรม</button>
					<?php endif; ?>
				</div>

				<div class="modal fade" id="participantFormModal" tabindex="-1" aria-label="เพิ่มผู้เข้าอบรม" aria-hidden="true">
					<div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
						<div class="modal-content">
					<form class="participant-form participant-form--modal" method="post" action="<?php echo base_url('index.php/dashboard/participants/'.$registration->registration_id); ?>">
						<input type="hidden" name="action" value="add">
						<div class="participant-form__head">
							<h3>ข้อมูลผู้เข้าอบรม</h3>
							<span><?php echo $is_full ? 'เต็มจำนวนแล้ว' : count($participants).' รายชื่อ'; ?></span>
						</div>

						<div class="modal-body participant-form__grid">
							<?php if ($is_full): ?>
								<div class="login-alert" role="alert">จำนวนผู้เข้าอบรมเต็มแล้ว ไม่สามารถเพิ่มรายชื่อได้</div>
							<?php endif; ?>
							<label>
								<span>ประเภท</span>
								<select name="participant_type" <?php echo $is_full ? 'disabled' : ''; ?>>
									<option value="student">นักเรียน / นักศึกษา</option>
									<option value="teacher">ครู / อาจารย์</option>
									<option value="staff">บุคลากร</option>
									<option value="other">อื่น ๆ</option>
								</select>
							</label>
							<label>
								<span>คำนำหน้า</span>
								<input type="text" name="title_name" placeholder="นาย / นางสาว / ดร." <?php echo $is_full ? 'disabled' : ''; ?>>
							</label>
							<label>
								<span>ชื่อ *</span>
								<input type="text" name="first_name" required <?php echo $is_full ? 'disabled' : ''; ?>>
							</label>
							<label>
								<span>นามสกุล *</span>
								<input type="text" name="last_name" required <?php echo $is_full ? 'disabled' : ''; ?>>
							</label>
							<label>
								<span>รหัสนักเรียน/นักศึกษา</span>
								<input type="text" name="student_code" <?php echo $is_full ? 'disabled' : ''; ?>>
							</label>
							<label>
								<span>หน่วยงาน / โรงเรียน</span>
								<input type="text" name="school_name" <?php echo $is_full ? 'disabled' : ''; ?>>
							</label>
							<label>
								<span>โทรศัพท์</span>
								<input type="text" name="phone" <?php echo $is_full ? 'disabled' : ''; ?>>
							</label>
							<label>
								<span>อีเมล</span>
								<input type="email" name="email" <?php echo $is_full ? 'disabled' : ''; ?>>
							</label>
						</div>

						<div class="modal-footer participant-form__actions">
							<button type="button" class="participant-modal-cancel" data-bs-dismiss="modal">ยกเลิก</button>
							<button class="btn" type="submit" <?php echo $is_full ? 'disabled' : ''; ?>><?php echo $is_full ? 'เต็ม' : 'เพิ่มผู้เข้าอบรม'; ?></button>
						</div>
					</form>
						</div>
					</div>
				</div>

				<div class="participant-layout participant-layout--single">
					<div class="participant-list">
						<div class="participant-list__head">
							<h3>รายชื่อผู้เข้าอบรม</h3>
							<span><?php echo count($participants); ?> คน</span>
						</div>

						<?php if (!empty($participants)): ?>
							<div class="participant-table" role="table" aria-label="รายชื่อผู้เข้าอบรม">
								<div class="participant-table__row participant-table__row--head" role="row">
									<span>ชื่อ-นามสกุล</span>
									<span>หน่วยงาน</span>
									<span>ติดต่อ</span>
									<span></span>
								</div>
								<?php foreach ($participants as $participant): ?>
									<?php
									$participant_name = trim((isset($participant->title_name) ? $participant->title_name : '').(isset($participant->first_name) ? $participant->first_name : '').' '.(isset($participant->last_name) ? $participant->last_name : ''));
									?>
									<div class="participant-table__row" role="row">
										<div>
											<strong><?php echo html_escape($participant_name); ?></strong>
											<?php if (!empty($participant->student_code)): ?>
												<small><?php echo html_escape($participant->student_code); ?></small>
											<?php endif; ?>
										</div>
										<span><?php echo html_escape(!empty($participant->school_name) ? $participant->school_name : '-'); ?></span>
										<div>
											<span><?php echo html_escape(!empty($participant->phone) ? $participant->phone : '-'); ?></span>
											<?php if (!empty($participant->email)): ?>
												<small><?php echo html_escape($participant->email); ?></small>
											<?php endif; ?>
										</div>
										<form class="participant-delete-form" method="post" action="<?php echo base_url('index.php/dashboard/participants/'.$registration->registration_id); ?>" data-participant-name="<?php echo html_escape($participant_name); ?>">
											<input type="hidden" name="action" value="delete">
											<input type="hidden" name="participant_id" value="<?php echo (int) $participant->id; ?>">
											<button class="participant-delete" type="submit">ลบ</button>
										</form>
									</div>
								<?php endforeach; ?>
							</div>
						<?php else: ?>
							<div class="dashboard-empty dashboard-empty--compact">
								<strong>ยังไม่มีรายชื่อผู้เข้าอบรม</strong>
								<span>เพิ่มรายชื่อจากแบบฟอร์มด้านซ้าย แล้วรายการจะแสดงที่นี่</span>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</section>
		</div>
	</main>

<?php $this->load->view('frontend/layouts/footer', array(
	'footer_scripts' => '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.querySelectorAll(".participant-delete-form").forEach(function (form) {
	form.addEventListener("submit", function (event) {
		event.preventDefault();

		var participantName = form.getAttribute("data-participant-name") || "รายการนี้";

		Swal.fire({
			title: "ยืนยันการลบ?",
			text: "ต้องการลบ " + participantName + " ออกจากรายชื่อผู้เข้าอบรมหรือไม่",
			icon: "warning",
			showCancelButton: true,
			confirmButtonColor: "#d33",
			cancelButtonColor: "#6c757d",
			confirmButtonText: "ลบ",
			cancelButtonText: "ยกเลิก"
		}).then(function (result) {
			if (result.isConfirmed) {
				form.submit();
			}
		});
	});
});
</script>'
)); ?>
