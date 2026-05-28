<?php
$hero_stats = array_merge(
	array(
		'open_courses' => 0,
		'active_categories' => 0,
		'open_batches' => 0,
		'registrations' => 0
	),
	isset($hero_stats) && is_array($hero_stats) ? $hero_stats : array()
);
?>
	<section class="hero">
		<div class="section hero__inner">
			<div>
				<div class="kicker">เปิดรับสมัครหลักสูตรระยะสั้นและอบรมเฉพาะทาง</div>
				<h2>ยกระดับทักษะวิทยาศาสตร์ เทคโนโลยี และนวัตกรรมเพื่อชุมชน</h2>
				<p class="hero__copy">ค้นหาโปรแกรมอบรมของคณะฯ เลือกรูปแบบที่เหมาะกับบุคคลทั่วไป นักเรียน นักศึกษา บุคลากร และหน่วยงานที่ต้องการพัฒนาศักยภาพทีมงาน</p>
				<div class="hero__actions">
					<a class="btn" href="#programs">สำรวจหลักสูตร</a>
					<a class="btn btn--dark" href="<?php echo base_url('index.php/auth/register'); ?>">ขอจัดอบรมเฉพาะหน่วยงาน</a>
				</div>
			</div>
			<div class="hero__panel" aria-label="สรุปข้อมูลการอบรม">
				<div class="stat-grid">
					<div class="stat">
						<strong><?php echo number_format((int) $hero_stats['open_courses']); ?></strong>
						<span>หลักสูตรที่เปิดใช้งาน</span>
					</div>
					<div class="stat">
						<strong><?php echo number_format((int) $hero_stats['active_categories']); ?></strong>
						<span>หมวดหมู่หลักสูตร</span>
					</div>
					<div class="stat">
						<strong><?php echo number_format((int) $hero_stats['open_batches']); ?></strong>
						<span>รอบอบรมที่เปิดรับสมัคร</span>
					</div>
					<div class="stat">
						<strong><?php echo number_format((int) $hero_stats['registrations']); ?></strong>
						<span>รายการสมัครอบรมสะสม</span>
					</div>
				</div>
			</div>
		</div>
	</section>
