	<?php
	$member = $this->session->userdata('member');
	$member_name = is_array($member) && !empty($member['full_name']) ? $member['full_name'] : '';
	?>
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
				<?php if ($member_name !== ''): ?>
					<a class="btn btn--account" href="<?php echo base_url('index.php/dashboard/profile'); ?>">บัญชีของฉัน</a>
					<a class="btn btn--logout" href="<?php echo base_url('index.php/auth/logout'); ?>">ออกจากระบบ</a>
				<?php else: ?>
					<a class="btn btn--dark" href="<?php echo base_url('index.php/auth/login'); ?>">เข้าสู่ระบบ</a>
					<a class="btn" href="<?php echo base_url('index.php#programs'); ?>">ดูหลักสูตรอบรม</a>
				<?php endif; ?>

			</div>
		</div>
	</header>

