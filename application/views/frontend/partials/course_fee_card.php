<?php
$fee_card_id='course-fee-'.(int)$course->registration_id;
$fee_locked=!empty($course->fee_change_locked);
?>
<section class="member-course__fee-card <?= $fee_locked?'member-course__fee-card--locked':'' ?>" aria-labelledby="<?= $fee_card_id ?>-title">
    <div class="member-course__fee-heading">
        <div>
            <span class="member-course__fee-eyebrow">อัตราค่าสมัคร</span>
            <h4 id="<?= $fee_card_id ?>-title">เงื่อนไขราคาสำหรับทั้งใบสมัคร</h4>
        </div>
        <span class="member-course__fee-badge"><?= $fee_locked?'ไม่สามารถเปลี่ยนได้':'เปลี่ยนได้ก่อนแจ้งชำระเงิน' ?></span>
    </div>
    <div class="member-course__fee-current">
        <div><span>เงื่อนไขปัจจุบัน</span><strong><?= html_escape($course->fee_label_snapshot) ?></strong></div>
        <p><strong><?= number_format($fee_per_person,2) ?></strong> บาท/คน <span aria-hidden="true">×</span> <?= number_format($participant_count) ?> คน</p>
    </div>
    <?php if (!$fee_locked): ?>
        <form class="member-course__fee-form js-course-fee-form" method="post" action="<?= site_url('dashboard/fee/'.(int)$course->registration_id) ?>"
              data-current-option="<?= (int)$course->fee_option_id ?>" data-people="<?= (int)$participant_count ?>" data-current-total="<?= training_money_cents($payment_total) ?>">
            <input type="hidden" name="fee_token" value="<?= html_escape($fee_token) ?>">
            <?php if ($this->config->item('csrf_protection')): ?><input type="hidden" name="<?= html_escape($this->security->get_csrf_token_name()) ?>" value="<?= html_escape($this->security->get_csrf_hash()) ?>"><?php endif; ?>
            <fieldset class="member-course__fee-fieldset" aria-describedby="<?= $fee_card_id ?>-help">
                <legend>เลือกเงื่อนไขราคา</legend>
                <div class="member-course__fee-options">
                <?php foreach($course->fee_choices as $choice): $is_current=(string)$course->fee_option_id===(string)$choice['id']; ?>
                    <label class="member-course__fee-option">
                        <input type="radio" name="fee_option_id" value="<?= (int)$choice['id'] ?>" data-amount="<?= training_money_cents($choice['amount']) ?>" <?= $is_current?'checked':'' ?> required>
                        <span class="member-course__fee-option-content">
                            <span class="member-course__fee-option-name"><?= html_escape($choice['label']) ?><?php if ($is_current): ?><small>ปัจจุบัน</small><?php endif; ?></span>
                            <span class="member-course__fee-option-price"><strong><?= number_format((float)$choice['amount'],2) ?></strong><small>บาท / คน</small></span>
                        </span>
                    </label>
                <?php endforeach; ?>
                </div>
            </fieldset>
            <p class="member-course__fee-help" id="<?= $fee_card_id ?>-help">เลือก 1 เงื่อนไข ใช้กับผู้เข้าอบรมทุกคนในใบสมัครนี้</p>
            <div class="member-course__fee-preview" data-fee-preview hidden role="status" aria-live="polite" aria-atomic="true">
                <div><span>ยอดรวมปัจจุบัน</span><strong><?= number_format((float)$payment_total,2) ?> บาท</strong></div>
                <span class="member-course__fee-preview-arrow" aria-hidden="true">→</span>
                <div><span>ยอดรวมหลังเปลี่ยน</span><strong data-fee-total></strong><small data-fee-calculation></small></div>
            </div>
            <div class="member-course__fee-actions">
                <p data-fee-message>เลือกเงื่อนไขที่ต้องการ แล้วกดบันทึกเพื่อปรับยอดของทั้งใบสมัคร</p>
                <div><button class="member-course__fee-reset" type="reset">คืนค่าเดิม</button><button class="btn member-course__fee-save" type="submit">บันทึกเงื่อนไขราคา</button></div>
            </div>
        </form>
    <?php else: ?>
        <p class="member-course__fee-locked-note">รายการนี้ไม่สามารถเปลี่ยนเงื่อนไขราคาได้ หากต้องการแก้ไข กรุณาติดต่อเจ้าหน้าที่</p>
    <?php endif; ?>
</section>
