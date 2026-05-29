<?php
$member = isset($member) && is_array($member) ? $member : array();
$full_name = trim((isset($member['title_name']) ? $member['title_name'] : '').(isset($member['first_name']) ? $member['first_name'] : '').' '.(isset($member['last_name']) ? $member['last_name'] : ''));
$avatar_text = $full_name !== ''
	? (function_exists('mb_substr') ? mb_substr($full_name, 0, 1, 'UTF-8') : substr($full_name, 0, 1))
	: 'M';

$this->load->view('frontend/layouts/header', array(
	'page_title' => 'บัญชีของฉัน | โปรแกรมการอบรม',
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
					<a class="is-active" href="<?php echo base_url('index.php/dashboard/profile'); ?>">บัญชีของฉัน</a>
					<a href="<?php echo base_url('index.php/dashboard/courses'); ?>">หลักสูตรเข้าอบรม</a>
					<a href="<?php echo base_url('index.php/home/calendar'); ?>">ปฏิทินอบรม</a>
					<a class="profile-menu__logout" href="<?php echo base_url('index.php/auth/logout'); ?>">ออกจากระบบ</a>
				</nav>
			</aside>

			<section class="profile-panel" aria-label="ข้อมูลของฉัน">
				<div class="profile-panel__head">
					<div>
						<h2>ข้อมูลของฉัน</h2>
						<p>จัดการข้อมูลส่วนตัวเพื่อใช้ในการสมัครและติดต่อเกี่ยวกับการอบรม</p>
					</div>
				</div>

				<?php if (!empty($error)): ?>
					<div class="login-alert" role="alert"><?php echo html_escape($error); ?></div>
				<?php endif; ?>

				<?php if (!empty($success)): ?>
					<div class="login-alert login-alert--success" role="status"><?php echo html_escape($success); ?></div>
				<?php endif; ?>

				<form class="profile-form" action="<?php echo base_url('index.php/dashboard/profile'); ?>" method="post">
					<div class="profile-form__fields">
						<label for="email">อีเมล</label>
						<div class="profile-static"><?php echo html_escape(isset($member['email']) ? $member['email'] : ''); ?></div>

						<label class="required-label" for="title_name">คำนำหน้า</label>
						<input id="title_name" type="text" name="title_name" value="<?php echo html_escape(isset($member['title_name']) ? $member['title_name'] : ''); ?>" required>

						<label class="required-label" for="first_name">ชื่อ</label>
						<input id="first_name" type="text" name="first_name" value="<?php echo html_escape(isset($member['first_name']) ? $member['first_name'] : ''); ?>" required>

						<label class="required-label" for="last_name">นามสกุล</label>
						<input id="last_name" type="text" name="last_name" value="<?php echo html_escape(isset($member['last_name']) ? $member['last_name'] : ''); ?>" required>

						<label class="required-label" for="position_name">ตำแหน่ง</label>
						<input id="position_name" type="text" name="position_name" value="<?php echo html_escape(isset($member['position_name']) ? $member['position_name'] : ''); ?>" required>

						<label class="required-label" for="organization_name">หน่วยงาน</label>
						<input id="organization_name" type="text" name="organization_name" value="<?php echo html_escape(isset($member['organization_name']) ? $member['organization_name'] : ''); ?>" required>

						<label class="required-label" for="phone">เบอร์โทรศัพท์</label>
						<input id="phone" type="tel" name="phone" value="<?php echo html_escape(isset($member['phone']) ? $member['phone'] : ''); ?>" required>
					</div>

					<div class="profile-photo">
						<div class="profile-photo__avatar"><?php echo html_escape($avatar_text); ?></div>
						<strong><?php echo html_escape($full_name); ?></strong>
						<span>รูปประจำตัวจะใช้ตัวอักษรแรกของชื่อสมาชิก</span>
					</div>

					<div class="profile-form__actions">
						<button class="btn" type="submit">บันทึก</button>
					</div>
				</form>
			</section>
		</div>
	</main>

<?php $this->load->view('frontend/layouts/footer'); ?>
