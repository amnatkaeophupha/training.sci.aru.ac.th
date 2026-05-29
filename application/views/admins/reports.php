<?php
$this->load->view('admins/layouts/header');
$this->load->view('admins/layouts/sidebar');

$overview = array_merge(array(
    'registrations' => 0,
    'participants' => 0,
    'revenue_uploaded' => 0,
    'pending_slips' => 0
), isset($overview) && is_array($overview) ? $overview : array());
$course_report = isset($course_report) && is_array($course_report) ? $course_report : array();
$payment_report = isset($payment_report) && is_array($payment_report) ? $payment_report : array();
$recent_registrations = isset($recent_registrations) && is_array($recent_registrations) ? $recent_registrations : array();
$tables_ready = isset($tables_ready) ? (bool) $tables_ready : FALSE;
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
$format_money = function ($amount) {
    return number_format((float) $amount, 2).' บาท';
};
?>
        <div class="admin-content">
            <header class="admin-topbar d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 px-3 px-lg-4 py-3 border-bottom">
                <div>
                    <h1 class="h4 mb-1">รายงาน</h1>
                    <p class="mb-0 text-secondary">สรุปจำนวนผู้ลงทะเบียน ผู้เข้าอบรม และสถานะการชำระเงิน</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <button class="btn btn-outline-primary" type="button" onclick="window.print()">พิมพ์รายงาน</button>
                    <a class="btn btn-dark" href="<?= site_url('admin/logout'); ?>">ออกจากระบบ</a>
                </div>
            </header>

            <main class="admin-main py-4">
                <?php if (!$tables_ready): ?>
                    <div class="alert alert-warning fw-semibold" role="alert">
                        ยังไม่พบตารางข้อมูลหลักสำหรับรายงาน กรุณารัน SQL ของระบบลงทะเบียนก่อน
                    </div>
                <?php endif; ?>

                <section class="row row-cols-1 row-cols-md-2 row-cols-xl-4 g-3 mb-4" aria-label="ภาพรวมรายงาน">
                    <div class="col">
                        <article class="admin-stat-card card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <span class="text-secondary small fw-bold">ใบสมัครทั้งหมด</span>
                                <strong class="d-block fs-2 lh-1 my-2"><?= number_format((int) $overview['registrations']); ?></strong>
                                <p class="mb-0 text-secondary small">รายการจาก training_registrations</p>
                            </div>
                        </article>
                    </div>
                    <div class="col">
                        <article class="admin-stat-card stat-green card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <span class="text-secondary small fw-bold">ผู้เข้าอบรม</span>
                                <strong class="d-block fs-2 lh-1 my-2"><?= number_format((int) $overview['participants']); ?></strong>
                                <p class="mb-0 text-secondary small">รายชื่อที่ active</p>
                            </div>
                        </article>
                    </div>
                    <div class="col">
                        <article class="admin-stat-card stat-blue card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <span class="text-secondary small fw-bold">ยอดสลิปที่อัปโหลด</span>
                                <strong class="d-block fs-4 lh-sm my-2"><?= html_escape($format_money($overview['revenue_uploaded'])); ?></strong>
                                <p class="mb-0 text-secondary small">สถานะรอตรวจสอบ/ชำระแล้ว</p>
                            </div>
                        </article>
                    </div>
                    <div class="col">
                        <article class="admin-stat-card stat-yellow card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <span class="text-secondary small fw-bold">รอตรวจสลิป</span>
                                <strong class="d-block fs-2 lh-1 my-2"><?= number_format((int) $overview['pending_slips']); ?></strong>
                                <p class="mb-0 text-secondary small">รายการ payment status 2</p>
                            </div>
                        </article>
                    </div>
                </section>

                <section class="row g-4">
                    <div class="col-xl-8">
                        <article class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center gap-3">
                                <h2 class="h5 mb-0">รายงานตามหลักสูตร</h2>
                                <a class="btn btn-sm btn-outline-primary" href="<?= site_url('admin/courses'); ?>">จัดการหลักสูตร</a>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>หลักสูตร</th>
                                                <th>ใบสมัคร</th>
                                                <th>ผู้เข้าอบรม</th>
                                                <th>ยอดสลิป</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($course_report)): ?>
                                                <tr><td colspan="4" class="text-center text-secondary py-4">ยังไม่มีข้อมูลรายงานหลักสูตร</td></tr>
                                            <?php endif; ?>
                                            <?php foreach ($course_report as $row): ?>
                                                <tr>
                                                    <td>
                                                        <strong class="d-block"><?= html_escape($row->course_title); ?></strong>
                                                        <span class="small text-secondary"><?= html_escape(!empty($row->category_name) ? $row->category_name : '-'); ?></span>
                                                    </td>
                                                    <td><?= number_format((int) $row->registration_count); ?></td>
                                                    <td><?= number_format((int) $row->participant_count); ?></td>
                                                    <td><?= html_escape($format_money($row->uploaded_amount)); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </article>
                    </div>

                    <div class="col-xl-4">
                        <article class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white">
                                <h2 class="h5 mb-0">รายงานสถานะชำระเงิน</h2>
                            </div>
                            <div class="card-body">
                                <?php if (empty($payment_report)): ?>
                                    <div class="alert alert-secondary mb-0">ยังไม่มีข้อมูลการชำระเงิน</div>
                                <?php endif; ?>
                                <div class="d-grid gap-3">
                                    <?php foreach ($payment_report as $row): ?>
                                        <?php $status = (int) $row->status; ?>
                                        <div class="border rounded-3 p-3">
                                            <span class="small text-secondary fw-bold"><?= html_escape(isset($payment_labels[$status]) ? $payment_labels[$status] : 'สถานะ '.$status); ?></span>
                                            <div class="d-flex justify-content-between gap-3 mt-1">
                                                <strong><?= number_format((int) $row->payment_count); ?> รายการ</strong>
                                                <strong class="text-primary"><?= html_escape($format_money($row->total_amount)); ?></strong>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </article>
                    </div>

                    <div class="col-12">
                        <article class="card border-0 shadow-sm">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center gap-3">
                                <h2 class="h5 mb-0">รายการสมัครล่าสุด</h2>
                                <a class="btn btn-sm btn-outline-primary" href="<?= site_url('admin/registrations'); ?>">ดูผู้ลงทะเบียนทั้งหมด</a>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>เลขที่สมัคร</th>
                                                <th>ผู้สมัคร</th>
                                                <th>หลักสูตร</th>
                                                <th>ผู้เข้าอบรม</th>
                                                <th>ยอดสลิป</th>
                                                <th>สถานะ</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($recent_registrations)): ?>
                                                <tr><td colspan="6" class="text-center text-secondary py-4">ยังไม่มีรายการสมัคร</td></tr>
                                            <?php endif; ?>
                                            <?php foreach ($recent_registrations as $registration): ?>
                                                <?php
                                                $status = (int) $registration->status;
                                                $member_name = trim($registration->title_name.$registration->first_name.' '.$registration->last_name);
                                                ?>
                                                <tr>
                                                    <td>
                                                        <a class="fw-bold" href="<?= site_url('admin/registrations/view/'.(int) $registration->id); ?>"><?= html_escape($registration->registration_code); ?></a>
                                                        <span class="d-block small text-secondary"><?= !empty($registration->created_at) ? html_escape(date('Y-m-d H:i', strtotime($registration->created_at))) : '-'; ?></span>
                                                    </td>
                                                    <td>
                                                        <strong class="d-block"><?= html_escape($member_name !== '' ? $member_name : '-'); ?></strong>
                                                        <span class="small text-secondary"><?= html_escape($registration->email ?: '-'); ?></span>
                                                    </td>
                                                    <td>
                                                        <?= html_escape($registration->course_title); ?>
                                                        <span class="d-block small text-secondary"><?= html_escape($registration->batch_no ?: '-'); ?></span>
                                                    </td>
                                                    <td><?= number_format((int) $registration->participant_count); ?> คน</td>
                                                    <td><?= html_escape($format_money($registration->uploaded_amount)); ?></td>
                                                    <td><?= html_escape(isset($registration_labels[$status]) ? $registration_labels[$status] : $status); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </article>
                    </div>
                </section>
            </main>

<?php $this->load->view('admins/layouts/footer'); ?>
