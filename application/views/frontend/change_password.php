<?php
$member = isset($member) && is_array($member) ? $member : array();
$success = isset($success) ? $success : '';
$error = isset($error) ? $error : '';
$full_name = trim((isset($member['title_name']) ? $member['title_name'] : '').(isset($member['first_name']) ? $member['first_name'] : '').' '.(isset($member['last_name']) ? $member['last_name'] : ''));
$avatar_text = $full_name !== ''
	? (function_exists('mb_substr') ? mb_substr($full_name, 0, 1, 'UTF-8') : substr($full_name, 0, 1))
	: 'M';

$this->load->view('frontend/layouts/header', array(
	'page_title' => 'เปลี่ยนรหัสผ่าน | โปรแกรมการอบรม',
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
					<a class="is-active" href="<?php echo base_url('index.php/dashboard/change_password'); ?>">เปลี่ยนรหัสผ่าน</a>
					<a href="<?php echo base_url('index.php/dashboard/courses'); ?>">หลักสูตรเข้าอบรม</a>
					<!-- <a href="#">ประวัติการอบรม</a> -->
					<a class="profile-menu__logout" href="<?php echo base_url('index.php/auth/logout'); ?>">ออกจากระบบ</a>
				</nav>
			</aside>

			<section class="profile-panel" aria-label="เปลี่ยนรหัสผ่าน">
				<div class="profile-panel__head">
					<div>
						<h2>เปลี่ยนรหัสผ่าน</h2>
						<p>กรอกรหัสผ่านเดิมและตั้งรหัสผ่านใหม่สำหรับเข้าสู่ระบบครั้งถัดไป</p>
					</div>
				</div>

				<?php if (!empty($error)): ?>
					<div class="login-alert" role="alert"><?php echo html_escape($error); ?></div>
				<?php endif; ?>

				<?php if (!empty($success)): ?>
					<div class="login-alert login-alert--success" role="status"><?php echo html_escape($success); ?></div>
				<?php endif; ?>

				<form class="profile-form profile-form--password" action="<?php echo base_url('index.php/dashboard/change_password'); ?>" method="post">
					<div class="profile-form__fields">
						<label class="required-label" for="current_password">รหัสผ่านเดิม</label>
						<input id="current_password" type="password" name="current_password" autocomplete="current-password" required>

						<label class="required-label" for="new_password">รหัสผ่านใหม่</label>
						<input id="new_password" type="password" name="new_password" minlength="8" autocomplete="new-password" required>

						<label class="required-label" for="confirm_password">ยืนยันรหัสผ่านใหม่</label>
						<input id="confirm_password" type="password" name="confirm_password" minlength="8" autocomplete="new-password" required>
					</div>

					<div class="profile-photo">
						<div class="profile-photo__avatar"><?php echo html_escape($avatar_text); ?></div>
						<strong><?php echo html_escape($full_name); ?></strong>
						<span>รหัสผ่านใหม่ต้องมีอย่างน้อย 8 ตัวอักษร</span>
					</div>

					<div class="profile-form__actions">
						<button class="btn" type="submit">บันทึกรหัสผ่านใหม่</button>
					</div>
				</form>
			</section>
		</div>
	</main>

	<div class="page-footer-spacer" aria-hidden="true"></div>

<?php $this->load->view('frontend/layouts/footer'); ?>
