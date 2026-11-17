<?php
$this->load->view('admins/layouts/header');
$this->load->view('admins/layouts/sidebar');
$rate=$report->eligible_count?$report->completed_count*100/$report->eligible_count:0;
?>
<div class="admin-content">
    <header class="admin-topbar px-3 px-lg-4 py-3 border-bottom">
        <h1 class="h4 mb-1">รายงานผลแบบประเมิน</h1>
        <p class="mb-0 text-secondary"><?= html_escape($report->course_title) ?> / <?= html_escape($report->batch_no) ?></p>
    </header>
    <main class="admin-main py-4">
        <div class="d-flex gap-2 mb-4">
            <a class="btn btn-outline-secondary" href="<?= site_url('admin/surveys/assignment/'.$report->id) ?>">กลับไป QR</a>
            <a class="btn btn-primary" href="<?= site_url('admin/surveys/export/'.$report->id) ?>">ส่งออก CSV</a>
        </div>
        <div class="row g-3 mb-4">
            <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body"><span>ผู้มีสิทธิ์</span><strong class="display-6 d-block"><?= $report->eligible_count ?></strong></div></div></div>
            <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body"><span>ตอบแล้ว</span><strong class="display-6 d-block"><?= $report->completed_count ?></strong></div></div></div>
            <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body"><span>อัตราการตอบ</span><strong class="display-6 d-block"><?= number_format($rate,1) ?>%</strong></div></div></div>
        </div>
        <?php foreach($report->questions as $i=>$q): ?>
            <div class="card border-0 shadow-sm mb-3"><div class="card-body">
                <h2 class="h5"><?= ($i+1).'. '.html_escape($q->question_text) ?></h2>
                <?php if($q->question_type==='rating'): $stats=$q->rating_stats; ?>
                    <div class="row g-3 mt-1 mb-3">
                        <div class="col-md-4">
                            <div class="bg-light rounded p-3 h-100">
                                <span class="text-secondary small">ค่าเฉลี่ย (Mean)</span>
                                <p class="display-6 mb-0"><?= $stats['mean']!==NULL?number_format($stats['mean'],2):'—' ?> <small class="fs-6 text-secondary">/ 5</small></p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="bg-primary-subtle rounded p-3 h-100">
                                <span class="text-primary small">มัธยฐาน (Median)</span>
                                <p class="display-6 text-primary mb-0"><?= $stats['median']!==NULL?number_format($stats['median'],2):'—' ?> <small class="fs-6">/ 5</small></p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="bg-light rounded p-3 h-100">
                                <span class="text-secondary small">คำตอบที่ใช้คำนวณ</span>
                                <p class="display-6 mb-0"><?= number_format($stats['count']) ?> <small class="fs-6 text-secondary">คน</small></p>
                            </div>
                        </div>
                    </div>
                    <p class="small text-secondary mb-3">มัธยฐานคือคะแนนตรงกลางหลังเรียงจากน้อยไปมาก หากมีคำตอบจำนวนคู่ จะใช้ค่าเฉลี่ยของสองคะแนนตรงกลาง</p>
                    <?php for($score=1;$score<=5;$score++): ?>
                        <div><?= $score ?> คะแนน: <?= $stats['counts'][$score] ?></div>
                    <?php endfor; ?>
                <?php elseif(in_array($q->question_type,array('single_choice','multiple_choice'))): ?>
                    <?php $counts=array(); foreach($q->answers as $a) if($a->option_text) $counts[$a->option_text]=isset($counts[$a->option_text])?$counts[$a->option_text]+1:1; ?>
                    <?php foreach($counts as $label=>$count): ?><div class="mb-1"><strong><?= html_escape($label) ?></strong>: <?= $count ?></div><?php endforeach; ?>
                <?php else: ?>
                    <?php foreach($q->answers as $a): if(trim($a->text_value)!==''): ?><div class="border rounded p-2 mb-2"><?= nl2br(html_escape($a->text_value)) ?></div><?php endif; endforeach; ?>
                <?php endif; ?>
            </div></div>
        <?php endforeach; ?>
    </main>
<?php $this->load->view('admins/layouts/footer'); ?>
