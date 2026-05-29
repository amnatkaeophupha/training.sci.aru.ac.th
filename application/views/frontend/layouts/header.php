<!DOCTYPE html>
<html lang="th">
<head>
	<?php
	$page_title = isset($page_title) ? $page_title : 'โปรแกรมการอบรม | คณะวิทยาศาสตร์และเทคโนโลยี';
	$body_class = isset($body_class) ? $body_class : 'page-home';
	?>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo html_escape($page_title); ?></title>
	<link rel="icon" href="<?php echo base_url('assets/images/logos/iconsci-removebg.png'); ?>" type="image/x-icon">
	<!-- Google Web Fonts -->
	<link href="https://fonts.googleapis.com/css?family=Roboto+Condensed:300italic,400italic,700italic,400,300,700" rel="stylesheet" type="text/css">
	<link href="https://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,800italic,400,300,600,700,800" rel="stylesheet" type="text/css">
	<link href="https://fonts.googleapis.com/css?family=Chakra+Petch|Sarabun|Kodchasan" rel="stylesheet">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<?php if (!empty($extra_head)): ?>
		<?php echo $extra_head; ?>
	<?php endif; ?>
	<link href="<?php echo base_url('assets/css/training.css?v=course-image-20260528'); ?>" rel="stylesheet" type="text/css">
</head>
<body class="<?php echo html_escape($body_class); ?>">
