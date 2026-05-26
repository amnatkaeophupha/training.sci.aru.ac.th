<!DOCTYPE html>
<html lang="th">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>สมัครสมาชิกใหม่ | โปรแกรมการอบรม</title>
	<link rel="icon" href="<?php echo base_url('assets/images/logos/iconsci-removebg.png'); ?>" type="image/x-icon">
	<link href="https://fonts.googleapis.com/css?family=Chakra+Petch|Sarabun|Kodchasan" rel="stylesheet">
	<link href="<?php echo base_url('assets/css/training.css'); ?>" rel="stylesheet" type="text/css">
</head>
<body class="page-login">
	<main class="login-page">
		<section class="login-shell login-shell--wide" aria-label="สมัครสมาชิกใหม่">
			<a class="login-brand" href="<?php echo base_url('index.php'); ?>">
				<img src="<?php echo base_url('assets/images/logos/aru-logo-h90.png'); ?>" alt="ตรามหาวิทยาลัยราชภัฏพระนครศรีอยุธยา">
				<span>
					<strong>Science & Technology Training</strong>
					<small>คณะวิทยาศาสตร์และเทคโนโลยี</small>
				</span>
			</a>

			<div class="login-card">
				<div class="login-card__head">
					<p>สร้างบัญชีผู้เข้าอบรม</p>
					<h1>สมัครสมาชิกใหม่</h1>
				</div>

				<?php if (!empty($error)): ?>
					<div class="login-alert" role="alert"><?php echo html_escape($error); ?></div>
				<?php endif; ?>

				<?php if (!empty($success)): ?>
					<div class="login-alert login-alert--success" role="status"><?php echo html_escape($success); ?></div>
				<?php endif; ?>

				<form class="login-form register-form" action="<?php echo base_url('index.php/auth/register'); ?>" method="post">
					<div class="form-row form-row--name">
						<div class="form-field">
							<label class="required-label" for="title_name">คำนำหน้า</label>
							<input id="title_name" type="text" name="title_name" value="<?php echo html_escape($title_name); ?>" placeholder="เช่น นาย, นางสาว, ดร." autocomplete="honorific-prefix" required>
						</div>
						<div class="form-field">
							<label class="required-label" for="first_name">ชื่อ</label>
							<input id="first_name" type="text" name="first_name" value="<?php echo html_escape($first_name); ?>" placeholder="กรอกชื่อ" autocomplete="given-name" required>
						</div>
						<div class="form-field">
							<label class="required-label" for="last_name">นามสกุล</label>
							<input id="last_name" type="text" name="last_name" value="<?php echo html_escape($last_name); ?>" placeholder="กรอกนามสกุล" autocomplete="family-name" required>
						</div>
					</div>

					<div class="form-row">
						<div class="form-field">
							<label class="required-label" for="position_name">ตำแหน่งของท่าน</label>
							<input id="position_name" type="text" name="position_name" value="<?php echo html_escape($position_name); ?>" placeholder="เช่น อาจารย์, นักศึกษา, เจ้าหน้าที่" autocomplete="organization-title" required>
						</div>
						<div class="form-field">
							<label class="required-label" for="organization_name">ชื่อหน่วยงาน</label>
							<input id="organization_name" type="text" name="organization_name" value="<?php echo html_escape($organization_name); ?>" placeholder="กรอกชื่อหน่วยงาน/สังกัด" autocomplete="organization" required>
						</div>
					</div>

					<div class="form-row">
						<div class="form-field">
							<label class="required-label" for="email">อีเมล</label>
							<input id="email" type="email" name="email" value="<?php echo html_escape($email); ?>" placeholder="name@example.com" autocomplete="email" required>
						</div>
						<div class="form-field">
							<label class="required-label" for="phone">เบอร์โทรศัพท์</label>
							<input id="phone" type="tel" name="phone" value="<?php echo html_escape($phone); ?>" placeholder="08x-xxx-xxxx" autocomplete="tel" required>
						</div>
					</div>

					<div class="form-row">
						<div class="form-field">
							<label class="required-label" for="password">รหัสผ่าน</label>
							<input id="password" type="password" name="password" placeholder="อย่างน้อย 8 ตัวอักษร" autocomplete="new-password" required>
						</div>
						<div class="form-field">
							<label class="required-label" for="confirm_password">ยืนยันรหัสผ่าน</label>
							<input id="confirm_password" type="password" name="confirm_password" placeholder="กรอกรหัสผ่านอีกครั้ง" autocomplete="new-password" required>
						</div>
					</div>

					<label class="login-check register-consent">
						<input type="checkbox" name="accept_terms" value="1" required>
						<span>ยืนยันว่าข้อมูลถูกต้องและยินยอมให้ใช้ข้อมูลเพื่อการสมัครอบรม</span>
					</label>

					<button class="btn" type="submit">สมัครสมาชิก</button>
				</form>

				<div class="auth-switch">
					มีบัญชีอยู่แล้ว? <a href="<?php echo base_url('index.php/auth/login'); ?>">เข้าสู่ระบบ</a>
				</div>
			</div>
		</section>
	</main>
</body>
</html>
