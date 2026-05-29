<?php
$this->load->view('admins/layouts/header');
$this->load->view('admins/layouts/sidebar');

$registration = isset($registration) && is_object($registration) ? $registration : (object) array();
$participants = isset($participants) && is_array($participants) ? $participants : array();
$payments = isset($payments) && is_array($payments) ? $payments : array();
$registration_labels = array(
    1 => 'รอชำระเงิน / รออนุมัติ',
    2 => 'อนุมัติแล้ว',
    3 => 'ไม่อนุมัติ',
    4 => 'ยกเลิก',
    5 => 'เข้าอบรมแล้ว'
);
$payment_labels = array(
    1 => 'รอชำระเงิน',
    2 => 'รอตรวจสอบสลิป',
    3 => 'ชำระเงินแล้ว',
    4 => 'ไม่ผ่านการตรวจสอบ'
);
$payment_badges = array(
    1 => 'text-bg-warning',
    2 => 'text-bg-primary',
    3 => 'text-bg-success',
    4 => 'text-bg-danger'
);
$format_money = function ($amount) {
    return number_format((float) $amount, 2).' บาท';
};
$member_name = trim((isset($registration->title_name) ? $registration->title_name : '').(isset($registration->first_name) ? $registration->first_name : '').' '.(isset($registration->last_name) ? $registration->last_name : ''));
$participant_count = max(1, count($participants));
$total_amount = (float) (isset($registration->fee) ? $registration->fee : 0) * $participant_count;
?>
        <div class="admin-content">
            <header class="admin-topbar d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 px-3 px-lg-4 py-3 border-bottom">
                <div>
                    <h1 class="h4 mb-1">รายละเอียดผู้ลงทะเบียน</h1>
                    <p class="mb-0 text-secondary"><?= html_escape(isset($registration->registration_code) ? $registration->registration_code : '-'); ?></p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a class="btn btn-outline-dark" href="<?= site_url('admin/registrations'); ?>">กลับรายการ</a>
                    <a class="btn btn-dark" href="<?= site_url('admin/logout'); ?>">ออกจากระบบ</a>
                </div>
            </header>

            <main class="admin-main py-4">
                <?php if ($this->session->flashdata('success')): ?>
                    <div class="alert alert-success fw-semibold" role="status"><?= html_escape($this->session->flashdata('success')); ?></div>
                <?php endif; ?>

                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger fw-semibold" role="alert"><?= html_escape($this->session->flashdata('error')); ?></div>
                <?php endif; ?>

                <section class="row g-4 mb-4">
                    <div class="col-lg-8">
                        <article class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white">
                                <h2 class="h5 mb-0">ข้อมูลการสมัคร</h2>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <span class="small text-secondary fw-bold">ผู้สมัคร</span>
                                        <strong class="d-block"><?= html_escape($member_name !== '' ? $member_name : '-'); ?></strong>
                                        <span class="text-secondary small"><?= html_escape(isset($registration->email) ? $registration->email : '-'); ?></span>
                                    </div>
                                    <div class="col-md-6">
                                        <span class="small text-secondary fw-bold">หลักสูตร</span>
                                        <strong class="d-block"><?= html_escape(isset($registration->course_title) ? $registration->course_title : '-'); ?></strong>
                                        <span class="text-secondary small"><?= html_escape(isset($registration->batch_no) ? $registration->batch_no : '-'); ?></span>
                                    </div>
                                    <div class="col-md-4">
                                        <span class="small text-secondary fw-bold">จำนวนผู้เข้าอบรม</span>
                                        <strong class="d-block"><?= number_format($participant_count); ?> คน</strong>
                                    </div>
                                    <div class="col-md-4">
                                        <span class="small text-secondary fw-bold">ค่าลงทะเบียน/คน</span>
                                        <strong class="d-block"><?= html_escape($format_money(isset($registration->fee) ? $registration->fee : 0)); ?></strong>
                                    </div>
                                    <div class="col-md-4">
                                        <span class="small text-secondary fw-bold">ยอดที่ควรชำระ</span>
                                        <strong class="d-block text-primary"><?= html_escape($format_money($total_amount)); ?></strong>
                                    </div>
                                </div>
                            </div>
                        </article>
                    </div>

                    <div class="col-lg-4">
                        <article class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white">
                                <h2 class="h5 mb-0">ปรับสถานะใบสมัคร</h2>
                            </div>
                            <div class="card-body">
                                <form method="post" action="<?= site_url('admin/registrations/update-status/'.(int) $registration->id); ?>">
                                    <label class="form-label fw-bold" for="registration_status">สถานะ</label>
                                    <select class="form-select mb-3" id="registration_status" name="status">
                                        <?php foreach ($registration_labels as $status => $label): ?>
                                            <option value="<?= (int) $status; ?>" <?= (int) $registration->status === (int) $status ? 'selected' : ''; ?>><?= html_escape($label); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button class="btn btn-primary w-100" type="submit">บันทึกสถานะ</button>
                                </form>
                            </div>
                        </article>
                    </div>
                </section>

                <section class="row g-4">
                    <div class="col-lg-7">
                        <article class="card border-0 shadow-sm">
                            <div class="card-header bg-white">
                                <h2 class="h5 mb-0">รายชื่อผู้เข้าอบรม</h2>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>ชื่อ-นามสกุล</th>
                                                <th>หน่วยงาน</th>
                                                <th>ติดต่อ</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($participants)): ?>
                                                <tr><td colspan="3" class="text-center text-secondary py-4">ยังไม่มีรายชื่อผู้เข้าอบรม</td></tr>
                                            <?php endif; ?>
                                            <?php foreach ($participants as $participant): ?>
                                                <?php $participant_name = trim($participant->title_name.$participant->first_name.' '.$participant->last_name); ?>
                                                <tr>
                                                    <td>
                                                        <strong class="d-block"><?= html_escape($participant_name); ?></strong>
                                                        <span class="small text-secondary"><?= html_escape(!empty($participant->participant_type) ? $participant->participant_type : '-'); ?></span>
                                                    </td>
                                                    <td><?= html_escape(!empty($participant->school_name) ? $participant->school_name : '-'); ?></td>
                                                    <td>
                                                        <?= html_escape(!empty($participant->phone) ? $participant->phone : '-'); ?>
                                                        <?php if (!empty($participant->email)): ?>
                                                            <br><span class="small text-secondary"><?= html_escape($participant->email); ?></span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </article>
                    </div>

                    <div class="col-lg-5">
                        <article class="card border-0 shadow-sm">
                            <div class="card-header bg-white">
                                <h2 class="h5 mb-0">รายการชำระเงิน</h2>
                            </div>
                            <div class="card-body">
                                <?php if (empty($payments)): ?>
                                    <div class="alert alert-secondary mb-0">ยังไม่มีรายการชำระเงิน</div>
                                <?php endif; ?>

                                <div class="d-grid gap-3">
                                    <?php foreach ($payments as $payment): ?>
                                        <?php $payment_status = (int) $payment->status; ?>
                                        <div class="border rounded-3 p-3">
                                            <div class="d-flex align-items-start justify-content-between gap-3 mb-2">
                                                <div>
                                                    <strong class="d-block"><?= html_escape($payment->payment_code); ?></strong>
                                                    <span class="small text-secondary"><?= html_escape($format_money($payment->amount)); ?></span>
                                                </div>
                                                <span class="badge <?= isset($payment_badges[$payment_status]) ? $payment_badges[$payment_status] : 'text-bg-secondary'; ?>">
                                                    <?= html_escape(isset($payment_labels[$payment_status]) ? $payment_labels[$payment_status] : $payment_status); ?>
                                                </span>
                                            </div>

                                            <?php if (!empty($payment->payment_slip)): ?>
                                                <a class="btn btn-sm btn-outline-primary mb-3" href="<?= base_url($payment->payment_slip); ?>" target="_blank" rel="noopener">ดูสลิป</a>
                                            <?php endif; ?>

                                            <form class="row g-2" method="post" action="<?= site_url('admin/registrations/update-payment/'.(int) $registration->id.'/'.(int) $payment->id); ?>">
                                                <div class="col-8">
                                                    <select class="form-select form-select-sm" name="status">
                                                        <?php foreach ($payment_labels as $status => $label): ?>
                                                            <?php if ((int) $status <= 0) { continue; } ?>
                                                            <option value="<?= (int) $status; ?>" <?= $payment_status === (int) $status ? 'selected' : ''; ?>><?= html_escape($label); ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="col-4">
                                                    <button class="btn btn-sm btn-primary w-100" type="submit">บันทึก</button>
                                                </div>
                                            </form>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </article>
                    </div>
                </section>
            </main>

<?php $this->load->view('admins/layouts/footer'); ?>
