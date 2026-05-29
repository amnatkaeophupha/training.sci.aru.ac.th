	<?php
	$member = $this->session->userdata('member');
	$member_name = is_array($member) && !empty($member['full_name']) ? $member['full_name'] : '';
	?>
	<div class="topbar">
		<div class="topbar__inner">
			<div class="topbar__links">
				<a href="<?php echo base_url('index.php'); ?>">หน้าแรก</a>
				<a href="#">ข่าวสาร</a>
				<a href="#">คลังความรู้</a>
				<a href="#">ติดต่อคณะ</a>
			</div>
			<div class="topbar__contact">
				<?php if ($member_name !== ''): ?>
					<a href="<?php echo base_url('index.php/dashboard/profile'); ?>"><?php echo html_escape($member_name); ?></a>
					|
					<a href="<?php echo base_url('index.php/dashboard/profile'); ?>">บัญชีของฉัน</a>
					|
					<a href="<?php echo base_url('index.php/auth/logout'); ?>">ออกจากระบบ</a>
				<?php else: ?>
					training@sci.aru.ac.th |
					<a href="<?php echo base_url('index.php/admin'); ?>">Admin</a>
				<?php endif; ?>
			</div>
		</div>
	</div>
