<?php
$instructors = isset($instructors) && is_array($instructors) ? $instructors : array();

$this->load->view('frontend/layouts/header', array(
	'page_title' => 'วิทยากร | คณะวิทยาศาสตร์และเทคโนโลยี',
	'body_class' => 'page-instructors'
));
$this->load->view('frontend/layouts/topbar');
$this->load->view('frontend/layouts/site_header');
$this->load->view('frontend/layouts/nav');
?>

	<section class="hero">
		<div class="section hero__inner">
			<div>
				<div class="breadcrumb"><a href="<?php echo base_url('index.php'); ?>">หน้าหลัก</a> / วิทยากร</div>
				<h2>ทีมวิทยากรและผู้เชี่ยวชาญ</h2>
				<p class="hero__copy">รวมรายชื่อวิทยากรที่ร่วมถ่ายทอดความรู้ในหลักสูตรอบรมของคณะวิทยาศาสตร์และเทคโนโลยี</p>
			</div>
			<div class="summary-card">
				<!-- <strong><?php echo number_format(count($instructors)); ?></strong> -->
				<span>วิทยากรที่พร้อมถ่ายทอดความรู้</span>
				<p>ข้อมูลจากระบบจัดการวิทยากร</p>
				<div class="actions">
					<a class="btn" href="<?php echo base_url('index.php#programs'); ?>">ดูหลักสูตรอบรม</a>
				</div>
			</div>
		</div>
	</section>

	<main class="main">
		<section class="section">
			<div class="section__head">
				<div>
					<h3>รายชื่อวิทยากร</h3>
					<p>เลือกดูข้อมูลความเชี่ยวชาญ หน่วยงาน และช่องทางติดต่อของวิทยากรแต่ละท่าน</p>
				</div>
			</div>

			<?php if (!empty($instructors)): ?>
				<div class="instructor-grid">
					<?php foreach ($instructors as $instructor): ?>
						<?php
						$name = !empty($instructor->name) ? $instructor->name : 'วิทยากร';
						$initial = function_exists('mb_substr') ? mb_substr($name, 0, 1, 'UTF-8') : substr($name, 0, 1);
						$photo = !empty($instructor->photo) ? $instructor->photo : '';
						?>
						<article class="instructor-card">
							<div class="instructor-card__photo">
								<?php if ($photo !== ''): ?>
									<img src="<?php echo base_url($photo); ?>" alt="<?php echo html_escape($name); ?>" loading="lazy" decoding="async">
								<?php else: ?>
									<span><?php echo html_escape($initial); ?></span>
								<?php endif; ?>
							</div>
							<div class="instructor-card__body">
								<h4><?php echo html_escape($name); ?></h4>
								<?php if (!empty($instructor->position)): ?>
									<p class="instructor-card__role"><?php echo html_escape($instructor->position); ?></p>
								<?php endif; ?>
								<?php if (!empty($instructor->department)): ?>
									<p class="instructor-card__meta"><?php echo html_escape($instructor->department); ?></p>
								<?php endif; ?>
								<?php if (!empty($instructor->bio)): ?>
									<p class="instructor-card__bio"><?php echo nl2br(html_escape($instructor->bio)); ?></p>
								<?php endif; ?>
								<?php if (!empty($instructor->email) || !empty($instructor->phone)): ?>
									<div class="instructor-card__links">
										<?php if (!empty($instructor->email)): ?>
											<a href="mailto:<?php echo html_escape($instructor->email); ?>"><?php echo html_escape($instructor->email); ?></a>
										<?php endif; ?>
										<?php if (!empty($instructor->phone)): ?>
											<a href="tel:<?php echo html_escape($instructor->phone); ?>"><?php echo html_escape($instructor->phone); ?></a>
										<?php endif; ?>
									</div>
								<?php endif; ?>
							</div>
						</article>
					<?php endforeach; ?>
				</div>
			<?php else: ?>
				<div class="dashboard-empty">
					<strong>ยังไม่มีข้อมูลวิทยากร</strong>
					<span>เพิ่มข้อมูลวิทยากรที่ระบบผู้ดูแล แล้วตั้งค่าสถานะเป็น “ใช้งาน”</span>
				</div>
			<?php endif; ?>
		</section>
	</main>

	<div class="page-footer-spacer" aria-hidden="true"></div>

<?php $this->load->view('frontend/layouts/footer'); ?>
