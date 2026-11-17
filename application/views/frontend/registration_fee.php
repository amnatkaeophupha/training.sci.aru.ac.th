<?php
$this->load->view('frontend/layouts/header',array('page_title'=>'เลือกเงื่อนไขค่าสมัคร','body_class'=>'page-profile'));
$this->load->view('frontend/layouts/topbar');
$this->load->view('frontend/layouts/site_header');
$this->load->view('frontend/layouts/nav');
?>
<main class="main"><section class="section" style="max-width:760px;padding-top:2rem;padding-bottom:3rem">
    <h1>เลือกเงื่อนไขค่าสมัคร</h1>
    <h2><?= html_escape($course->title) ?></h2><p><?= html_escape($course->batch_no) ?></p>
    <p>เลือกหนึ่งเงื่อนไขสำหรับทั้งใบสมัคร ผู้เข้าอบรมทุกคนในใบสมัครนี้จะใช้ราคาเดียวกัน</p>
    <?php if ($error!==''): ?><div class="login-alert" role="alert"><?= html_escape($error) ?></div><?php endif; ?>
    <form method="post" action="<?= site_url('dashboard/courses?batch_id='.(int)$batch_id) ?>">
        <input type="hidden" name="fee_token" value="<?= html_escape($fee_token) ?>">
        <input type="hidden" name="pricing_revision" value="<?= (int)$course->pricing_revision ?>">
        <?php if ($this->config->item('csrf_protection')): ?><input type="hidden" name="<?= html_escape($this->security->get_csrf_token_name()) ?>" value="<?= html_escape($this->security->get_csrf_hash()) ?>"><?php endif; ?>
        <fieldset><legend>เงื่อนไขราคา</legend>
        <?php foreach($course->fee_options as $option): ?>
            <label style="display:block;border:1px solid #ccd7e3;border-radius:8px;padding:1rem;margin-bottom:.75rem">
                <input type="radio" name="fee_option_id" value="<?= (int)$option['id'] ?>" required>
                <?= html_escape($option['label']) ?> — <strong><?= number_format((float)$option['amount'],2) ?> บาท/คน</strong>
            </label>
        <?php endforeach; ?>
        </fieldset>
        <p>เริ่มต้นด้วยผู้เข้าอบรม 1 คน คุณเพิ่มรายชื่อได้หลังยืนยัน ยอดรวมจะคำนวณตามจำนวนผู้เข้าอบรม</p>
        <button class="btn" type="submit" <?= !$course->fee_options?'disabled':'' ?>>ยืนยันราคาและสมัครอบรม</button>
        <a class="btn" href="<?= site_url('home/detail/'.$course->slug) ?>">กลับไปหลักสูตร</a>
    </form>
</section></main>
<?php $this->load->view('frontend/layouts/footer'); ?>
