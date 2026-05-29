<!DOCTYPE html>
<html lang="th">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>ตั้งรหัสผ่านใหม่ | โปรแกรมการอบรม</title>
	<link rel="icon" href="<?php echo base_url('assets/images/logos/iconsci-removebg.png'); ?>" type="image/x-icon">
	<link href="https://fonts.googleapis.com/css?family=Chakra+Petch|Sarabun|Kodchasan" rel="stylesheet">
	<link href="<?php echo base_url('assets/css/training.css'); ?>" rel="stylesheet" type="text/css">
</head>
<body class="page-login">
	<main class="login-page">
		<section class="login-shell" aria-label="ตั้งรหัสผ่านใหม่">
			<a class="login-brand" href="<?php echo base_url('index.php'); ?>">
				<img src="<?php echo base_url('assets/images/logos/aru-logo-h90.png'); ?>" alt="ตรามหาวิทยาลัยราชภัฏพระนครศรีอยุธยา">
				<span>
					<strong>Science & Technology Training</strong>
					<small>คณะวิทยาศาสตร์และเทคโนโลยี</small>
				</span>
			</a>

			<div class="login-card">
				<div class="login-card__head">
					<p>ตั้งค่าความปลอดภัยบัญชี</p>
					<h1>ตั้งรหัสผ่านใหม่</h1>
					<span class="login-help">กำหนดรหัสผ่านใหม่อย่างน้อย 8 ตัวอักษร เพื่อใช้เข้าสู่ระบบครั้งถัดไป</span>
				</div>

				<?php if (!empty($error)): ?>
					<div class="login-alert" role="alert"><?php echo html_escape($error); ?></div>
				<?php endif; ?>

				<?php if (!empty($is_valid_token)): ?>
					<form class="login-form" action="<?php echo base_url('index.php/auth/reset-password/'.rawurlencode($token)); ?>" method="post">
						<label class="required-label" for="new_password">รหัสผ่านใหม่</label>
						<input id="new_password" type="password" name="new_password" placeholder="อย่างน้อย 8 ตัวอักษร" minlength="8" autocomplete="new-password" required>

						<label class="required-label" for="confirm_password">ยืนยันรหัสผ่านใหม่</label>
						<input id="confirm_password" type="password" name="confirm_password" placeholder="กรอกรหัสผ่านอีกครั้ง" minlength="8" autocomplete="new-password" required>

						<button class="btn" type="submit">บันทึกรหัสผ่านใหม่</button>
					</form>
				<?php else: ?>
					<div class="auth-switch auth-switch--no-border">
						<a href="<?php echo base_url('index.php/auth/forgot_password'); ?>">ขอลิงก์ตั้งรหัสผ่านใหม่อีกครั้ง</a>
					</div>
				<?php endif; ?>

				<div class="auth-switch">
					กลับไปหน้า <a href="<?php echo base_url('index.php/auth/login'); ?>">เข้าสู่ระบบ</a>
				</div>
			</div>
		</section>
	</main>
</body>
</html>
