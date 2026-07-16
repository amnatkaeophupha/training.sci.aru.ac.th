<?php
$this->load->view('admins/layouts/header');
$this->load->view('admins/layouts/sidebar');
$edit = isset($edit_survey) && $edit_survey ? $edit_survey : (object) array('id'=>0,'title'=>'','description'=>'','status'=>1);
?>
<div class="admin-content">
<header class="admin-topbar px-3 px-lg-4 py-3 border-bottom"><h1 class="h4 mb-1">แบบประเมินหลังอบรม</h1><p class="mb-0 text-secondary">สร้างชุดคำถามและนำกลับไปใช้กับหลักสูตรหรือรุ่นอบรมต่าง ๆ</p></header>
<main class="admin-main py-4">
<?php foreach (array('success'=>'success','error'=>'danger') as $key=>$class): if ($this->session->flashdata($key)): ?><div class="alert alert-<?= $class ?>"><?= html_escape($this->session->flashdata($key)) ?></div><?php endif; endforeach; ?>
<?php if (!$tables_ready): ?>
<div class="alert alert-warning">ยังไม่พบตารางแบบประเมิน กรุณารันไฟล์ <code>Design/training_surveys.sql</code> ก่อนใช้งาน</div>
<?php else: ?>
<div class="row g-4">
<div class="col-lg-4"><div class="card border-0 shadow-sm"><div class="card-body"><h2 class="h5"><?= $edit->id ? 'แก้ไข' : 'สร้าง' ?>แบบประเมิน</h2>
<form method="post" action="<?= site_url('admin/surveys/save/'.(int)$edit->id) ?>">
<label class="form-label mt-3">ชื่อแบบประเมิน *</label><input class="form-control" name="title" required value="<?= html_escape($edit->title) ?>">
<label class="form-label mt-3">คำอธิบาย</label><textarea class="form-control" name="description" rows="4"><?= html_escape($edit->description) ?></textarea>
<label class="form-label mt-3">สถานะ</label><select class="form-select" name="status"><option value="1" <?= $edit->status==1?'selected':'' ?>>ฉบับร่าง</option><option value="2" <?= $edit->status==2?'selected':'' ?>>ใช้งาน</option><option value="3" <?= $edit->status==3?'selected':'' ?>>เก็บถาวร</option></select>
<button class="btn btn-primary w-100 mt-3">บันทึก</button></form></div></div></div>
<div class="col-lg-8"><div class="card border-0 shadow-sm"><div class="card-body"><h2 class="h5">ชุดแบบประเมินทั้งหมด</h2><div class="table-responsive"><table class="table align-middle"><thead><tr><th>ชื่อ</th><th>คำถาม</th><th>เปิดใช้</th><th></th></tr></thead><tbody>
<?php foreach ($surveys as $s): ?><tr><td><strong><?= html_escape($s->title) ?></strong><small class="d-block text-secondary"><?= html_escape($s->description) ?></small></td><td><?= number_format($s->question_count) ?></td><td><?= number_format($s->assignment_count) ?></td><td class="text-nowrap"><a class="btn btn-sm btn-outline-secondary" href="<?= site_url('admin/surveys?edit='.$s->id) ?>">แก้ไข</a> <a class="btn btn-sm btn-outline-primary" href="<?= site_url('admin/surveys/questions/'.$s->id) ?>">คำถาม</a> <a class="btn btn-sm btn-success" href="<?= site_url('admin/surveys/assign/'.$s->id) ?>">เปิดใช้</a></td></tr><?php endforeach; ?>
<?php if (!$surveys): ?><tr><td colspan="4" class="text-center text-secondary py-4">ยังไม่มีแบบประเมิน</td></tr><?php endif; ?></tbody></table></div></div></div></div>
</div>
<?php if ($assignments): ?><div class="card border-0 shadow-sm mt-4"><div class="card-body"><h2 class="h5">รายการที่เปิดใช้ล่าสุด</h2><div class="table-responsive"><table class="table"><thead><tr><th>แบบประเมิน</th><th>หลักสูตร/รุ่น</th><th>ช่วงเวลา</th><th>ตอบแล้ว</th><th></th></tr></thead><tbody><?php foreach ($assignments as $a): ?><tr><td><?= html_escape($a->survey_title) ?></td><td><?= html_escape($a->course_title) ?> / <?= html_escape($a->batch_no) ?></td><td><?= html_escape($a->open_at) ?> – <?= html_escape($a->close_at) ?></td><td><?= $a->completed_count ?> / <?= $a->eligible_count ?></td><td><a class="btn btn-sm btn-outline-primary" href="<?= site_url('admin/surveys/assignment/'.$a->id) ?>">จัดการ</a></td></tr><?php endforeach; ?></tbody></table></div></div></div><?php endif; ?>
<?php endif; ?>
</main>
<?php $this->load->view('admins/layouts/footer'); ?>
