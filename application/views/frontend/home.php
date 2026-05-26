
<!DOCTYPE html>
<html lang="th">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>โปรแกรมการอบรม | คณะวิทยาศาสตร์และเทคโนโลยี</title>
	<link rel="icon" href="<?php echo base_url('assets/images/logos/iconsci-removebg.png'); ?>" type="image/x-icon">
	<!-- Google Web Fonts -->
	<link href="https://fonts.googleapis.com/css?family=Roboto+Condensed:300italic,400italic,700italic,400,300,700" rel="stylesheet" type="text/css">
	<link href="https://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,800italic,400,300,600,700,800" rel="stylesheet" type="text/css">
	<link href="https://fonts.googleapis.com/css?family=Chakra+Petch|Sarabun|Kodchasan" rel="stylesheet">
	<link href="<?php echo base_url('assets/css/training.css'); ?>" rel="stylesheet" type="text/css">
</head>
<body class="page-home">
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
				<div class="kicker">เปิดรับสมัครหลักสูตรระยะสั้นและอบรมเฉพาะทาง</div>
				<h2>ยกระดับทักษะวิทยาศาสตร์ เทคโนโลยี และนวัตกรรมเพื่อชุมชน</h2>
				<p class="hero__copy">ค้นหาโปรแกรมอบรมของคณะฯ เลือกรูปแบบที่เหมาะกับบุคคลทั่วไป นักเรียน นักศึกษา บุคลากร และหน่วยงานที่ต้องการพัฒนาศักยภาพทีมงาน</p>
				<div class="hero__actions">
					<a class="btn" href="#programs">สำรวจหลักสูตร</a>
					<a class="btn btn--dark" href="#">ขอจัดอบรมเฉพาะหน่วยงาน</a>
				</div>
			</div>
			<div class="hero__panel" aria-label="สรุปข้อมูลการอบรม">
				<div class="stat-grid">
					<div class="stat">
						<strong>24</strong>
						<span>หลักสูตรพร้อมเปิดอบรม</span>
					</div>
					<div class="stat">
						<strong>8</strong>
						<span>กลุ่มทักษะวิทยาศาสตร์และดิจิทัล</span>
					</div>
					<div class="stat">
						<strong>1,250+</strong>
						<span>ผู้ผ่านการอบรมสะสม</span>
					</div>
					<div class="stat">
						<strong>95%</strong>
						<span>ความพึงพอใจของผู้เข้าอบรม</span>
					</div>
				</div>
			</div>
		</div>
	</section>

	<main class="main">
		<section class="section" id="programs">
			<div class="filterbar" aria-label="ค้นหาและกรองหลักสูตร">
				<form class="search" action="#" method="get">
					<input type="search" name="q" placeholder="ค้นหาชื่อหลักสูตร เช่น Data, Food, Coding">
					<button type="submit" aria-label="ค้นหา">⌕</button>
				</form>
				<button class="pill pill--active" type="button">ทั้งหมด</button>
				<button class="pill" type="button">เปิดรับสมัคร</button>
				<button class="pill" type="button">ออนไลน์</button>
				<button class="pill" type="button">อบรมในพื้นที่</button>
			</div>

			<div class="section__head">
				<div>
					<h3>โปรแกรมการอบรมแนะนำ</h3>
					<p>ออกแบบหน้ารายการให้เห็นข้อมูลที่จำเป็นทันที ทั้งหัวข้อ วันอบรม ระยะเวลา รูปแบบ และสถานะการรับสมัคร</p>
				</div>
				<a class="course__link" href="#">ดูปฏิทินอบรมทั้งหมด</a>
			</div>

			<div class="program-grid">
				<article class="course">
					<div class="course__media">
						<span class="course__badge">Data Science</span>
					</div>
					<div class="course__body">
						<h4>การวิเคราะห์ข้อมูลด้วย Python สำหรับงานวิจัยและชุมชน</h4>
						<p>เรียนรู้การจัดการข้อมูล สร้างกราฟ และสรุปผลเชิงสถิติสำหรับงานวิชาการและงานบริการท้องถิ่น</p>
						<div class="meta">
							<span>2 วัน</span>
							<span>ห้องปฏิบัติการคอมพิวเตอร์</span>
							<span>รับ 30 คน</span>
						</div>
						<div class="course__footer">
							<span class="course__date">18-19 มิ.ย. 2569</span>
							<a class="course__link" href="<?php echo base_url('index.php/home/detail'); ?>">รายละเอียด</a>
						</div>
					</div>
				</article>

				<article class="course">
					<div class="course__media">
						<span class="course__badge">Food & Lab</span>
					</div>
					<div class="course__body">
						<h4>มาตรฐานห้องปฏิบัติการและความปลอดภัยทางอาหาร</h4>
						<p>เตรียมความพร้อมด้านเอกสาร การตรวจประเมิน และแนวปฏิบัติด้านความปลอดภัยสำหรับหน่วยงานและผู้ประกอบการ</p>
						<div class="meta">
							<span>1 วัน</span>
							<span>อบรมในพื้นที่</span>
							<span>มีใบประกาศ</span>
						</div>
						<div class="course__footer">
							<span class="course__date">25 มิ.ย. 2569</span>
							<a class="course__link" href="<?php echo base_url('index.php/home/detail'); ?>">รายละเอียด</a>
						</div>
					</div>
				</article>

				<article class="course">
					<div class="course__media">
						<span class="course__badge">Digital Skill</span>
					</div>
					<div class="course__body">
						<h4>สร้างสื่อดิจิทัลและระบบลงทะเบียนสำหรับงานอบรม</h4>
						<p>พัฒนาทักษะการออกแบบสื่อประชาสัมพันธ์ แบบฟอร์มรับสมัคร และแดชบอร์ดติดตามผลสำหรับผู้จัดโครงการ</p>
						<div class="meta">
							<span>ออนไลน์</span>
							<span>3 ชั่วโมง</span>
							<span>เหมาะกับบุคลากร</span>
						</div>
						<div class="course__footer">
							<span class="course__date">2 ก.ค. 2569</span>
							<a class="course__link" href="<?php echo base_url('index.php/home/detail'); ?>">รายละเอียด</a>
						</div>
					</div>
				</article>
			</div>
		</section>

		<section class="info-band">
			<div class="section info-band__inner">
				<div>
					<h3>รองรับทั้งการสมัครรายบุคคลและการจัดอบรมเฉพาะหน่วยงาน</h3>
					<p>หน้าเว็บนี้วางโครงให้ต่อยอดเป็นระบบจริงได้ เช่น หน้ารายละเอียดหลักสูตร แบบฟอร์มสมัคร ตรวจสอบรายชื่อ และรายงานผู้เข้าอบรม</p>
				</div>
				<div class="steps">
					<div class="step">
						<strong>1</strong>
						เลือกหลักสูตรและรอบอบรม
					</div>
					<div class="step">
						<strong>2</strong>
						กรอกข้อมูลผู้สมัครหรือหน่วยงาน
					</div>
					<div class="step">
						<strong>3</strong>
						ติดตามสถานะและรับเอกสารยืนยัน
					</div>
				</div>
			</div>
		</section>
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
