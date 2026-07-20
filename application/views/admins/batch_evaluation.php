<?php
$this->load->view('admins/layouts/header');
$this->load->view('admins/layouts/sidebar');
$open=$assignment?date('Y-m-d\TH:i',strtotime($assignment->open_at)):$default_open;
$close=$assignment?date('Y-m-d\TH:i',strtotime($assignment->close_at)):$default_close;
$has_responses=$assignment&&(int)$stats->completed_count>0;
$url=$assignment?site_url('evaluation/'.$assignment->public_code):'';
?>
<div class="admin-content">
<header class="admin-topbar px-3 px-lg-4 py-3 border-bottom"><h1 class="h4 mb-1">แบบประเมินหลังอบรม</h1><p class="mb-0 text-secondary"><?= html_escape($batch->course_title.' / '.$batch->batch_no) ?></p></header>
<main class="admin-main py-4">
<?php if($this->session->flashdata('success')): ?><div class="alert alert-success"><?= html_escape($this->session->flashdata('success')) ?></div><?php endif; ?>
<?php if($this->session->flashdata('error')): ?><div class="alert alert-danger"><?= html_escape($this->session->flashdata('error')) ?></div><?php endif; ?>
<div class="row g-4"><div class="col-lg-7"><div class="card border-0 shadow-sm"><div class="card-body"><h2 class="h5">ตั้งค่าแบบประเมินของรุ่น</h2>
<form method="post" class="row g-3" id="batch-evaluation-form"><div class="col-12"><label class="form-label">แบบประเมิน *</label><select class="form-select" name="survey_id" required <?= $has_responses?'disabled':'' ?>><option value="">เลือกแบบประเมิน</option><?php foreach($surveys as $survey): ?><option value="<?= $survey->id ?>" <?= $assignment&&(int)$assignment->survey_id===(int)$survey->id?'selected':'' ?>><?= html_escape($survey->title) ?> (<?= (int)$survey->question_count ?> ข้อ)</option><?php endforeach; ?></select><?php if($has_responses): ?><input type="hidden" name="survey_id" value="<?= (int)$assignment->survey_id ?>"><div class="form-text text-warning">มีผู้ตอบแล้ว จึงเปลี่ยนชุดคำถามไม่ได้</div><?php endif; ?></div>
<?php foreach(array('open'=>array('เปิดตอบ',$open),'close'=>array('ปิดตอบ',$close)) as $key=>$field): ?>
<div class="col-md-6 js-thai-datetime" data-value="<?= html_escape($field[1]) ?>">
    <label class="form-label"><?= $field[0] ?> *</label>
    <input type="hidden" name="<?= $key ?>_at" class="js-thai-datetime-value" value="<?= html_escape($field[1]) ?>">
    <div class="row g-2">
        <div class="col-3 col-sm-2"><select class="form-select js-thai-day" aria-label="วัน"></select></div>
        <div class="col-9 col-sm-4"><select class="form-select js-thai-month" aria-label="เดือน"></select></div>
        <div class="col-7 col-sm-3"><select class="form-select js-thai-year" aria-label="ปี พ.ศ."></select></div>
        <div class="col-5 col-sm-3"><input class="form-control js-thai-time" type="time" aria-label="เวลา" step="60"></div>
    </div>
    <div class="form-text fw-semibold text-primary js-thai-date-label"></div>
</div>
<?php endforeach; ?>
<div class="col-12"><button class="btn btn-success">บันทึกการตั้งค่า</button> <a class="btn btn-outline-secondary" href="<?= site_url('admin/batches') ?>">กลับหน้ารุ่นอบรม</a></div></form></div></div></div>
<div class="col-lg-5"><?php if($assignment): ?><div class="card border-0 shadow-sm"><div class="card-body text-center"><h2 class="h5">QR Code แบบประเมิน</h2><div id="evaluation-qr" class="d-flex justify-content-center my-3"></div><p class="small text-break"><?= html_escape($url) ?></p><div class="fs-3 fw-bold"><?= (int)$stats->completed_count ?> / <?= (int)$stats->eligible_count ?></div><p class="text-secondary">ตอบแบบประเมินแล้ว</p><a class="btn btn-outline-primary" target="_blank" href="<?= $url ?>">เปิดหน้าสำหรับผู้เข้าอบรม</a></div></div><?php else: ?><div class="alert alert-info">บันทึกการตั้งค่าก่อน ระบบจึงจะสร้าง QR Code และสิทธิ์ผู้เข้าอบรม</div><?php endif; ?></div></div>
<script>
(function(){
var months=['มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน','กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'];
function option(value,text){var item=document.createElement('option');item.value=value;item.textContent=text;return item}
function pad(value){return String(value).padStart(2,'0')}
function setup(box){
    var hidden=box.querySelector('.js-thai-datetime-value'),day=box.querySelector('.js-thai-day'),month=box.querySelector('.js-thai-month'),year=box.querySelector('.js-thai-year'),time=box.querySelector('.js-thai-time'),label=box.querySelector('.js-thai-date-label');
    months.forEach(function(name,index){month.appendChild(option(index+1,name))});
    for(var buddhistYear=2500;buddhistYear<=2700;buddhistYear++)year.appendChild(option(buddhistYear,buddhistYear));
    var match=(hidden.value||'').match(/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2})$/),now=new Date();
    year.value=match?Number(match[1])+543:now.getFullYear()+543;
    month.value=match?Number(match[2]):now.getMonth()+1;
    time.value=match?match[4]+':'+match[5]:'00:00';
    function fillDays(selected){var count=new Date(Number(year.value)-543,Number(month.value),0).getDate();day.innerHTML='';for(var value=1;value<=count;value++)day.appendChild(option(value,value));day.value=Math.min(Number(selected)||1,count)}
    function update(){fillDays(day.value);var gregorian=Number(year.value)-543;hidden.value=gregorian+'-'+pad(month.value)+'-'+pad(day.value)+'T'+time.value;label.textContent=Number(day.value)+' '+months[Number(month.value)-1]+' '+year.value+' เวลา '+time.value+' น.'}
    fillDays(match?Number(match[3]):now.getDate());
    [day,month,year,time].forEach(function(input){input.addEventListener('change',update)});update();
}
document.querySelectorAll('.js-thai-datetime').forEach(setup);
document.getElementById('batch-evaluation-form').addEventListener('submit',function(event){var open=this.elements.open_at.value,close=this.elements.close_at.value,openTime=new Date(open).getTime(),closeTime=new Date(close).getTime();if(!open||!close||isNaN(openTime)||isNaN(closeTime)||closeTime<=openTime){event.preventDefault();alert('กรุณาเลือกวันเวลาให้ครบ และกำหนดเวลาปิดตอบให้อยู่หลังเวลาเปิดตอบ')}});
})();
</script>
<?php if($assignment): ?><script src="<?= base_url('assets/js/qrcode.min.js') ?>"></script><script>new QRCode(document.getElementById('evaluation-qr'),{text:<?= json_encode($url) ?>,width:220,height:220,correctLevel:QRCode.CorrectLevel.H});</script><?php endif; ?>
</main><?php $this->load->view('admins/layouts/footer'); ?>
