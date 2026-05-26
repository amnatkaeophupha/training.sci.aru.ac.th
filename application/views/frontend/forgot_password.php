<!DOCTYPE html>
<html lang="th">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>ลืมรหัสผ่าน | โปรแกรมการอบรม</title>
	<link rel="icon" href="<?php echo base_url('assets/images/logos/iconsci-removebg.png'); ?>" type="image/x-icon">
	<link href="https://fonts.googleapis.com/css?family=Chakra+Petch|Sarabun|Kodchasan" rel="stylesheet">
	<link href="<?php echo base_url('assets/css/training.css'); ?>" rel="stylesheet" type="text/css">
</head>
<body class="page-login">
	<main class="login-page">
		<section class="login-shell" aria-label="ลืมรหัสผ่าน">
			<a class="login-brand" href="<?php echo base_url('index.php'); ?>">
				<img src="<?php echo base_url('assets/images/logos/aru-logo-h90.png'); ?>" alt="ตรามหาวิทยาลัยราชภัฏพระนครศรีอยุธยา">
				<span>
					<strong>Science & Technology Training</strong>
					<small>คณะวิทยาศาสตร์และเทคโนโลยี</small>
				</span>
			</a>

			<div class="login-card">
				<div class="login-card__head">
					<p>กู้คืนบัญชีผู้ใช้งาน</p>
					<h1>ลืมรหัสผ่าน?</h1>
					<span class="login-help">กรอกอีเมลที่ใช้สมัครสมาชิก ระบบจะส่งขั้นตอนการตั้งรหัสผ่านใหม่ให้ท่าน</span>
				</div>

				<?php if (!empty($error)): ?>
					<div class="login-alert" role="alert"><?php echo html_escape($error); ?></div>
				<?php endif; ?>

				<?php if (!empty($success)): ?>
					<div class="login-alert login-alert--success" role="status"><?php echo html_escape($success); ?></div>
				<?php endif; ?>

				<form class="login-form" action="<?php echo base_url('index.php/auth/forgot_password'); ?>" method="post">
					<label class="required-label" for="email">อีเมลผู้ใช้งาน</label>
					<input id="email" type="email" name="email" value="<?php echo html_escape($email); ?>" placeholder="name@example.com" autocomplete="email" required>

					<button class="btn" type="submit">ส่งคำขอรีเซ็ตรหัสผ่าน</button>
				</form>

				<div class="auth-switch">
					จำรหัสผ่านได้แล้ว? <a href="<?php echo base_url('index.php/auth/login'); ?>">กลับไปเข้าสู่ระบบ</a>
				</div>
			</div>
		</section>
	</main>
</body>
</html>
