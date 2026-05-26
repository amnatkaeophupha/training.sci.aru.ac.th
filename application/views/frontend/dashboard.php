<?php
$avatar_text = function_exists('mb_substr')
	? mb_substr($member['full_name'], 0, 1, 'UTF-8')
	: substr($member['full_name'], 0, 1);
?>
<!DOCTYPE html>
<html lang="th">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Dashboard สมาชิก | โปรแกรมการอบรม</title>
	<link rel="icon" href="<?php echo base_url('assets/images/logos/iconsci-removebg.png'); ?>" type="image/x-icon">
	<link href="https://fonts.googleapis.com/css?family=Chakra+Petch|Sarabun|Kodchasan" rel="stylesheet">
	<link href="<?php echo base_url('assets/css/training.css?v=dashboard-20260520'); ?>" rel="stylesheet" type="text/css">
</head>
<body class="page-dashboard">
	<div class="dashboard-shell">
		<aside class="dashboard-sidebar">
			<a class="dashboard-brand" href="<?php echo base_url('index.php/dashboard'); ?>">
				<img src="<?php echo base_url('assets/images/logos/aru-logo-h90.png'); ?>" alt="ตรามหาวิทยาลัยราชภัฏพระนครศรีอยุธยา">
				<span>
					<strong>Science & Technology Training</strong>
					<small>ระบบสมาชิกผู้เข้าอบรม</small>
				</span>
			</a>

			<div class="dashboard-user">
				<div class="dashboard-avatar"><?php echo html_escape($avatar_text); ?></div>
				<strong><?php echo html_escape($member['full_name']); ?></strong>
				<span><?php echo html_escape($member['email']); ?></span>
			</div>

			<nav class="dashboard-menu" aria-label="เมนูสมาชิก">
				<a class="is-active" href="<?php echo base_url('index.php/dashboard'); ?>">แดชบอร์ด</a>
				<a href="<?php echo base_url('index.php#programs'); ?>">หลักสูตรอบรม</a>
				<a href="<?php echo base_url('index.php'); ?>">หน้าแรกเว็บไซต์</a>
				<a class="dashboard-menu__logout" href="<?php echo base_url('index.php/auth/logout'); ?>">ออกจากระบบ</a>
			</nav>
		</aside>

		<main class="dashboard-main">
			<header class="dashboard-hero">
				<div>
					<p>ยินดีต้อนรับกลับมา</p>
					<h1><?php echo html_escape($member['full_name']); ?></h1>
					<span>ติดตามหลักสูตรอบรม การสมัคร และประกาศสำคัญของท่านได้จากหน้านี้</span>
				</div>
				<a class="btn btn--dark" href="<?php echo base_url('index.php#programs'); ?>">เลือกหลักสูตรอบรม</a>
			</header>

			<section class="dashboard-grid" aria-label="ข้อมูลสมาชิก">
				<article class="dashboard-card">
					<p>สถานะบัญชี</p>
					<h2>พร้อมใช้งาน</h2>
					<span>บัญชีสมาชิกผ่านการลงทะเบียนเรียบร้อย</span>
				</article>

				<article class="dashboard-card">
					<p>หลักสูตรที่สมัคร</p>
					<h2>0 รายการ</h2>
					<span>ยังไม่มีรายการสมัครอบรมในขณะนี้</span>
				</article>

				<article class="dashboard-card">
					<p>การแจ้งเตือน</p>
					<h2>ไม่มีรายการใหม่</h2>
					<span>ประกาศและรายละเอียดหลักสูตรจะแสดงที่นี่</span>
				</article>
			</section>

			<section class="dashboard-content">
				<article class="dashboard-panel">
					<div class="dashboard-panel__head">
						<div>
							<p>รายการสมัครล่าสุด</p>
							<h2>การอบรมของฉัน</h2>
						</div>
						<a href="<?php echo base_url('index.php#programs'); ?>">ดูหลักสูตรทั้งหมด</a>
					</div>

					<div class="dashboard-empty">
						<strong>ยังไม่มีข้อมูลการสมัครของท่าน</strong>
						<span>เลือกหลักสูตรที่สนใจจากหน้ารายการอบรม ระบบจะแสดงสถานะการสมัครในหน้านี้</span>
					</div>
				</article>

				<aside class="dashboard-panel dashboard-next">
					<p>ขั้นตอนถัดไป</p>
					<ol>
						<li>เลือกหลักสูตรอบรมที่สนใจ</li>
						<li>ตรวจสอบรายละเอียด วันเวลา และสถานที่</li>
						<li>ส่งคำขอสมัครและรอการยืนยัน</li>
					</ol>
				</aside>
			</section>
		</main>
	</div>

	<footer class="footer dashboard-footer">
		<div class="footer__inner">
			<section class="footer__block" aria-label="ติดตามเรา">
				<h2>ติดตามเรา</h2>
				<div class="footer__socials">
					<a href="#" aria-label="Facebook">f</a>
					<a href="#" aria-label="Instagram">◎</a>
					<a href="#" aria-label="YouTube">▶</a>
					<a href="#" aria-label="Line">LINE</a>
				</div>
			</section>

			<section class="footer__block footer__contact" aria-label="ติดต่อเรา">
				<h2>ติดต่อเรา</h2>
				<p><span class="footer__icon">⌖</span>คณะวิทยาศาสตร์และเทคโนโลยี มหาวิทยาลัยราชภัฏพระนครศรีอยุธยา</p>
				<p><span class="footer__icon">☎</span>0 3527 6555</p>
				<p><span class="footer__icon">✉</span>training@sci.aru.ac.th</p>
			</section>
		</div>
		<div class="footer__bottom">
			© 2026 Science & Technology Training Center | ระบบสมาชิกผู้เข้าอบรม
		</div>
	</footer>
</body>
</html>
