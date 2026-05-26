
<!DOCTYPE html>
<html lang="th">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>รายละเอียดโปรแกรมอบรม | คณะวิทยาศาสตร์และเทคโนโลยี</title>
	<link rel="icon" href="<?php echo base_url('assets/images/logos/iconsci-removebg.png'); ?>" type="image/x-icon">
	<!-- Google Web Fonts -->
	<link href="https://fonts.googleapis.com/css?family=Roboto+Condensed:300italic,400italic,700italic,400,300,700" rel="stylesheet" type="text/css">
	<link href="https://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,800italic,400,300,600,700,800" rel="stylesheet" type="text/css">
	<link href="https://fonts.googleapis.com/css?family=Chakra+Petch|Sarabun|Kodchasan" rel="stylesheet">
	<link href="<?php echo base_url('assets/css/training.css'); ?>" rel="stylesheet" type="text/css">
</head>
<body class="page-detail">
	<div class="topbar">
		<div class="topbar__inner">
			<div class="topbar__links">
				<a href="<?php echo base_url('index.php'); ?>">หน้าแรก</a>
				<a href="#">ข่าวสาร</a>
				<a href="#">คลังความรู้</a> 
				<a href="#">ติดต่อคณะ</a>
			</div>
			<div class="topbar__contact">training@sci.aru.ac.th | 0 3527 6555</div>
		</div>
	</div>

	<header class="header">
		<div class="header__inner">
			<a class="brand" href="<?php echo base_url('index.php'); ?>">
				<img class="brand__mark" src="<?php echo base_url('assets/images/logos/aru-logo-h90.png'); ?>" alt="ตรามหาวิทยาลัยราชภัฏพระนครศรีอยุธยา">
				<span>
					<span class="brand__eyebrow">Science & Technology Training</span>
					<h1>คณะวิทยาศาสตร์และเทคโนโลยี</h1>
					<p>มหาวิทยาลัยราชภัฏพระนครศรีอยุธยา</p>
				</span>
			</a>
			<div class="header__actions">
				<a class="btn btn--dark" href="<?php echo base_url('index.php/auth/login'); ?>">เข้าสู่ระบบ</a>
				<a class="btn" href="<?php echo base_url('index.php#programs'); ?>">ดูหลักสูตรอบรม</a>
			</div>
		</div>
	</header>

	<nav class="nav" aria-label="เมนูหลัก">
		<div class="nav__inner">
			<div class="nav__links">
				<a href="<?php echo base_url('index.php'); ?>">หน้าหลัก</a>
				<a href="<?php echo base_url('index.php#programs'); ?>">โปรแกรมอบรม</a>
				<a href="#">ปฏิทินอบรม</a>
				<a href="#">วิทยากร</a>
				<a href="#">หน่วยงานเครือข่าย</a>
				<a href="#">คำถามที่พบบ่อย</a>
				<a href="#">ติดต่อสอบถาม</a>
			</div>
		</div>
	</nav>

	<section class="hero">
		<div class="section hero__inner">
			<div>
				<div class="breadcrumb"><a href="<?php echo base_url('index.php'); ?>">หน้าหลัก</a> / <a href="<?php echo base_url('index.php#programs'); ?>">โปรแกรมอบรม</a> / รายละเอียด</div>
				<h2>การวิเคราะห์ข้อมูลด้วย Python สำหรับงานวิจัยและชุมชน</h2>
				<p>เรียนรู้การจัดการข้อมูล สร้างกราฟ และสรุปผลเชิงสถิติสำหรับงานวิชาการ งานบริการวิชาการ และการพัฒนาชุมชนท้องถิ่น</p>
			</div>
			<div class="summary-card">
				<strong>Data Science</strong>
				<span>เปิดรับสมัคร</span>
				<p>18-19 มิ.ย. 2569</p>
				<div class="actions">
					<a class="btn" href="#register">สมัครอบรม</a>
					<a class="btn btn--light" href="<?php echo base_url('index.php#programs'); ?>">กลับรายการ</a>
				</div>
			</div>
		</div>
	</section>

	<main class="main">
		<div class="section detail-grid">
			<div class="content">
				<section class="panel">
					<h3>ข้อมูลหลักสูตร</h3>
					<div class="info-grid">
						<div class="info-item">
							<span>วันอบรม</span>
							<strong>18-19 มิ.ย. 2569</strong>
						</div>
						<div class="info-item">
							<span>ระยะเวลา</span>
							<strong>2 วัน</strong>
						</div>
						<div class="info-item">
							<span>รูปแบบ</span>
							<strong>ห้องปฏิบัติการคอมพิวเตอร์</strong>
						</div>
						<div class="info-item">
							<span>จำนวนรับ</span>
							<strong>รับ 30 คน</strong>
						</div>
						<div class="info-item">
							<span>ค่าลงทะเบียน</span>
							<strong>1,500 บาท</strong>
						</div>
						<div class="info-item">
							<span>ระดับหลักสูตร</span>
							<strong>เริ่มต้นถึงปานกลาง</strong>
						</div>
					</div>
				</section>

				<section class="panel">
					<h3>สิ่งที่จะได้เรียนรู้</h3>
					<ul class="list">
						<li>เข้าใจแนวคิดสำคัญของหลักสูตรและสามารถนำไปใช้กับงานจริงได้</li>
						<li>ฝึกปฏิบัติผ่านกรณีศึกษาและเครื่องมือที่เหมาะกับบริบทของผู้เข้าอบรม</li>
						<li>รับคำแนะนำจากวิทยากรของคณะวิทยาศาสตร์และเทคโนโลยี</li>
						<li>ได้รับเอกสารประกอบการอบรมและแนวทางต่อยอดหลังจบหลักสูตร</li>
					</ul>
				</section>

				<section class="panel">
					<h3>กลุ่มเป้าหมาย</h3>
					<p>นักศึกษา บุคลากร นักวิจัย และผู้สนใจทั่วไป</p>
				</section>
			</div>

			<aside class="aside" id="register">
				<section class="panel register-box">
					<span class="status">เปิดรับสมัคร</span>
					<h3>สมัครเข้าร่วมอบรม</h3>
					<p>ตรวจสอบข้อมูลรอบอบรมและเตรียมข้อมูลผู้สมัครให้พร้อมก่อนส่งแบบฟอร์ม ระบบส่วนนี้สามารถเชื่อมต่อแบบฟอร์มรับสมัครจริงได้ภายหลัง</p>
					<a class="btn" href="#">กรอกใบสมัคร</a>
					<a class="btn btn--light" href="<?php echo base_url('index.php#programs'); ?>">ดูหลักสูตรอื่น</a>
				</section>

				<section class="panel">
					<h3>ผู้รับผิดชอบหลักสูตร</h3>
					<p>ทีมวิทยากรคณะวิทยาศาสตร์และเทคโนโลยี</p>
				</section>
			</aside>
		</div>
	</main>


	<footer class="footer">
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
			© 2026 Science & Technology Training Center | สถิติเข้าชมเว็บไซต์ : 62,004 ครั้ง | วันนี้ : 74 ครั้ง
		</div>
	</footer>
</body>
</html>
