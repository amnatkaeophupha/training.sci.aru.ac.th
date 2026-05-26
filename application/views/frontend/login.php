<!DOCTYPE html>
<html lang="th">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>เข้าสู่ระบบ | โปรแกรมการอบรม</title>
	<link rel="icon" href="<?php echo base_url('assets/images/logos/iconsci-removebg.png'); ?>" type="image/x-icon">
	<link href="https://fonts.googleapis.com/css?family=Chakra+Petch|Sarabun|Kodchasan" rel="stylesheet">
	<link href="<?php echo base_url('assets/css/training.css'); ?>" rel="stylesheet" type="text/css">
</head>
<body class="page-login">
	<main class="login-page">
		<section class="login-shell" aria-label="เข้าสู่ระบบ">
			<a class="login-brand" href="<?php echo base_url('index.php'); ?>">
				<img src="<?php echo base_url('assets/images/logos/aru-logo-h90.png'); ?>" alt="ตรามหาวิทยาลัยราชภัฏพระนครศรีอยุธยา">
				<span>
					<strong>Science & Technology Training</strong>
					<small>คณะวิทยาศาสตร์และเทคโนโลยี</small>
				</span>
			</a>

			<div class="login-card">
				<div class="login-card__head">
					<p>ระบบจัดการข้อมูลการอบรม</p>
					<h1>เข้าสู่ระบบ</h1>
				</div>

				<?php if (!empty($error)): ?>
					<div class="login-alert" role="alert"><?php echo html_escape($error); ?></div>
				<?php endif; ?>

				<?php if (!empty($success)): ?>
					<div class="login-alert login-alert--success" role="status"><?php echo html_escape($success); ?></div>
				<?php endif; ?>

				<form class="login-form" action="<?php echo base_url('index.php/auth/login'); ?>" method="post">
					<label class="required-label" for="email">อีเมลผู้ใช้งาน</label>
					<input id="email" type="email" name="email" value="<?php echo html_escape($email); ?>" placeholder="name@example.com" autocomplete="username" required>

					<label class="required-label" for="password">รหัสผ่าน</label>
					<input id="password" type="password" name="password" placeholder="กรอกรหัสผ่าน" autocomplete="current-password" required>

					<div class="login-options">
						<label class="login-check">
							<input type="checkbox" name="remember" value="1">
							<span>จดจำการเข้าสู่ระบบ</span>
						</label>
						<a href="<?php echo base_url('index.php/auth/forgot_password'); ?>">ลืมรหัสผ่าน?</a>
					</div>

					<button class="btn" type="submit">เข้าสู่ระบบ</button>
				</form>

				<div class="auth-switch">
					ยังไม่มีบัญชี? <a href="<?php echo base_url('index.php/auth/register'); ?>">สมัครสมาชิกใหม่</a>
				</div>
			</div>
		</section>
	</main>
</body>
</html>
