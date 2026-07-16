<?php $this->load->view('frontend/evaluation_header'); ?>
<div class="card eval-card"><div class="card-body p-4 p-md-5"><h1 class="h3"><?= html_escape($assignment->survey_title) ?></h1><p class="text-secondary"><?= html_escape($assignment->description) ?></p>
<?php if($error): ?><div class="alert alert-danger"><?= html_escape($error) ?></div><?php endif; ?>
<form method="post" id="evaluation-form">
<?php foreach($questions as $index=>$q): ?><section class="question"><h2 class="h6 mb-3"><?= ($index+1).'. '.html_escape($q->question_text) ?><?php if($q->is_required): ?> <span class="text-danger">*</span><?php endif; ?></h2>
<?php if($q->question_type==='rating'): ?><div class="d-flex flex-wrap gap-2"><?php for($score=1;$score<=5;$score++): ?><label class="choice text-center flex-grow-1"><input type="radio" name="answer[<?= $q->id ?>]" value="<?= $score ?>" <?= $q->is_required?'required':'' ?>><strong class="d-block fs-4"><?= $score ?></strong></label><?php endfor; ?></div>
<?php elseif($q->question_type==='single_choice'): foreach($q->options as $o): ?><label class="choice"><input type="radio" name="answer[<?= $q->id ?>]" value="<?= $o->id ?>" <?= $q->is_required?'required':'' ?>> <?= html_escape($o->option_text) ?></label><?php endforeach;
elseif($q->question_type==='multiple_choice'): foreach($q->options as $o): ?><label class="choice"><input type="checkbox" name="answer[<?= $q->id ?>][]" value="<?= $o->id ?>"> <?= html_escape($o->option_text) ?></label><?php endforeach; if($q->is_required): ?><input class="multi-required" type="hidden" data-name="answer[<?= $q->id ?>][]"><?php endif;
elseif($q->question_type==='short_text'): ?><input class="form-control form-control-lg" name="answer[<?= $q->id ?>]" maxlength="500" <?= $q->is_required?'required':'' ?>>
<?php else: ?><textarea class="form-control" name="answer[<?= $q->id ?>]" rows="5" maxlength="5000" <?= $q->is_required?'required':'' ?>></textarea><?php endif; ?>
</section><?php endforeach; ?>
<button class="btn btn-success btn-lg w-100" type="submit" onclick="return confirm('ยืนยันส่งแบบประเมินหรือไม่? เมื่อส่งแล้วจะแก้ไขไม่ได้')">ส่งแบบประเมิน</button></form></div></div>
<script>document.getElementById('evaluation-form').addEventListener('submit',function(e){var ok=true;document.querySelectorAll('.multi-required').forEach(function(h){if(!document.querySelector('input[name="'+h.dataset.name+'"]:checked'))ok=false});if(!ok){e.preventDefault();alert('กรุณาตอบคำถามแบบเลือกหลายข้อที่บังคับให้ครบ')}});</script>
<?php $this->load->view('frontend/evaluation_footer'); ?>
