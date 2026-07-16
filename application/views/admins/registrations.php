<?php
$this->load->view('admins/layouts/header');
$this->load->view('admins/layouts/sidebar');

$registrations = isset($registrations) && is_array($registrations) ? $registrations : array();
$stats = array_merge(array('total' => 0, 'pending' => 0, 'approved' => 0, 'payment_checking' => 0), isset($stats) ? $stats : array());
$filters = isset($filters) && is_array($filters) ? $filters : array();
$courses = isset($courses) && is_array($courses) ? $courses : array();
$batches = isset($batches) && is_array($batches) ? $batches : array();
$filter_context = isset($filter_context) ? $filter_context : NULL;
$tables_ready = isset($tables_ready) ? (bool) $tables_ready : FALSE;
$registration_labels = array(
    1 => 'รอชำระเงิน / รออนุมัติ',
    2 => 'อนุมัติแล้ว',
    3 => 'ไม่อนุมัติ',
    4 => 'ยกเลิก',
    5 => 'เข้าอบรมแล้ว'
);
$registration_badges = array(
    1 => 'text-bg-warning',
    2 => 'text-bg-success',
    3 => 'text-bg-danger',
    4 => 'text-bg-secondary',
    5 => 'text-bg-info'
);
$payment_labels = array(
    0 => 'ไม่มีรายการ',
    1 => 'รอชำระเงิน',
    2 => 'รอตรวจสอบสลิป',
    3 => 'ชำระเงินแล้ว',
    4 => 'ไม่ผ่านการตรวจสอบ'
);
$payment_badges = array(
    0 => 'text-bg-secondary',
    1 => 'text-bg-warning',
    2 => 'text-bg-primary',
    3 => 'text-bg-success',
    4 => 'text-bg-danger'
);
$format_money = function ($amount) {
    return number_format((float) $amount, 2).' บาท';
};
?>
        <div class="admin-content">
            <header class="admin-topbar d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 px-3 px-lg-4 py-3 border-bottom">
                <div>
                    <h1 class="h4 mb-1">จัดการผู้ลงทะเบียน</h1>
                    <p class="mb-0 text-secondary">ตรวจสอบใบสมัคร รายชื่อผู้เข้าอบรม และสถานะการชำระเงิน</p>
                </div>
                <a class="btn btn-dark" href="<?= site_url('admin/logout'); ?>">ออกจากระบบ</a>
            </header>

            <main class="admin-main py-4">
                <?php if ($this->session->flashdata('success')): ?>
                    <div class="alert alert-success fw-semibold" role="status"><?= html_escape($this->session->flashdata('success')); ?></div>
                <?php endif; ?>

                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger fw-semibold" role="alert"><?= html_escape($this->session->flashdata('error')); ?></div>
                <?php endif; ?>

                <?php if (!$tables_ready): ?>
                    <div class="alert alert-warning fw-semibold" role="alert">
                        ยังไม่พบตารางลงทะเบียน กรุณารันไฟล์ SQL สำหรับ training_registrations ก่อน
                    </div>
                <?php endif; ?>

                <?php if ($filter_context): ?>
                    <section class="alert alert-primary border-0 shadow-sm mb-4" aria-label="ขอบเขตรายการผู้ลงทะเบียน">
                        <strong class="d-block fs-5"><?= html_escape($filter_context->course_title); ?></strong>
                        <?php if (!empty($filters['batch_id'])): ?>
                            <span class="d-block mt-1">
                                รุ่น <?= html_escape($filter_context->batch_no ?: '-'); ?>
                                <?php if (!empty($filter_context->start_date)): ?>
                                    · <?= html_escape(date('d/m/Y', strtotime($filter_context->start_date))); ?>
                                    <?php if (!empty($filter_context->end_date) && $filter_context->end_date !== $filter_context->start_date): ?>
                                        – <?= html_escape(date('d/m/Y', strtotime($filter_context->end_date))); ?>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </span>
                        <?php else: ?>
                            <span class="d-block mt-1">แสดงผู้ลงทะเบียนจากทุกรุ่นอบรมของหลักสูตรนี้</span>
                        <?php endif; ?>
                    </section>
                <?php endif; ?>

                <section class="row row-cols-1 row-cols-md-2 row-cols-xl-4 g-3 mb-4" aria-label="สรุปผู้ลงทะเบียน">
                    <div class="col">
                        <article class="admin-stat-card card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <span class="text-secondary small fw-bold">รายการทั้งหมด</span>
                                <strong class="d-block fs-2 lh-1 my-2"><?= number_format((int) $stats['total']); ?></strong>
                                <p class="mb-0 text-secondary small">ใบสมัครในระบบ</p>
                            </div>
                        </article>
                    </div>
                    <div class="col">
                        <article class="admin-stat-card stat-yellow card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <span class="text-secondary small fw-bold">รออนุมัติ</span>
                                <strong class="d-block fs-2 lh-1 my-2"><?= number_format((int) $stats['pending']); ?></strong>
                                <p class="mb-0 text-secondary small">รอชำระเงินหรือรอตรวจสอบ</p>
                            </div>
                        </article>
                    </div>
                    <div class="col">
                        <article class="admin-stat-card stat-green card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <span class="text-secondary small fw-bold">อนุมัติแล้ว</span>
                                <strong class="d-block fs-2 lh-1 my-2"><?= number_format((int) $stats['approved']); ?></strong>
                                <p class="mb-0 text-secondary small">รายการที่ผ่านการอนุมัติ</p>
                            </div>
                        </article>
                    </div>
                    <div class="col">
                        <article class="admin-stat-card stat-blue card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <span class="text-secondary small fw-bold">รอตรวจสลิป</span>
                                <strong class="d-block fs-2 lh-1 my-2"><?= number_format((int) $stats['payment_checking']); ?></strong>
                                <p class="mb-0 text-secondary small">รายการชำระเงิน status 2</p>
                            </div>
                        </article>
                    </div>
                </section>

                <article class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <form class="row g-2 align-items-end" method="get" action="<?= site_url('admin/registrations'); ?>">
                            <div class="col-md-6 col-xl-3">
                                <label class="form-label fw-bold" for="course_id">หลักสูตร</label>
                                <select class="form-select" id="course_id" name="course_id">
                                    <option value="0">ทุกหลักสูตร</option>
                                    <?php foreach ($courses as $course): ?>
                                        <option value="<?= (int) $course->id; ?>" <?= isset($filters['course_id']) && (int) $filters['course_id'] === (int) $course->id ? 'selected' : ''; ?>>
                                            <?= html_escape($course->title); ?> (<?= number_format((int) $course->registration_count); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 col-xl-2">
                                <label class="form-label fw-bold" for="batch_id">รุ่นอบรม</label>
                                <select class="form-select" id="batch_id" name="batch_id" <?= empty($filters['course_id']) ? 'disabled' : ''; ?>>
                                    <option value="0">ทุกรุ่น</option>
                                    <?php foreach ($batches as $batch): ?>
                                        <option value="<?= (int) $batch->id; ?>" <?= isset($filters['batch_id']) && (int) $filters['batch_id'] === (int) $batch->id ? 'selected' : ''; ?>>
                                            รุ่น <?= html_escape($batch->batch_no ?: '-'); ?> (<?= number_format((int) $batch->registration_count); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 col-xl-3">
                                <label class="form-label fw-bold" for="q">ค้นหา</label>
                                <input class="form-control" id="q" type="search" name="q" value="<?= html_escape(isset($filters['q']) ? $filters['q'] : ''); ?>" placeholder="เลขที่สมัคร ชื่อ อีเมล หรือหลักสูตร">
                            </div>
                            <div class="col-md-3 col-xl-2">
                                <label class="form-label fw-bold" for="status">สถานะ</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="0">ทุกสถานะ</option>
                                    <?php foreach ($registration_labels as $status => $label): ?>
                                        <option value="<?= (int) $status; ?>" <?= isset($filters['status']) && (int) $filters['status'] === (int) $status ? 'selected' : ''; ?>><?= html_escape($label); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3 col-xl-2 d-flex gap-2">
                                <button class="btn btn-primary flex-fill" type="submit">ค้นหา</button>
                                <a class="btn btn-outline-secondary" href="<?= site_url('admin/registrations'); ?>">ล้าง</a>
                            </div>
                        </form>
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
                                        <th>ยอดชำระ</th>
                                        <th>สถานะ</th>
                                        <th>ชำระเงิน</th>
                                        <th>จัดการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($registrations)): ?>
                                        <tr>
                                            <td colspan="8" class="text-center text-secondary py-4">ยังไม่มีรายการผู้ลงทะเบียน</td>
                                        </tr>
                                    <?php endif; ?>

                                    <?php foreach ($registrations as $registration): ?>
                                        <?php
                                        $status = (int) $registration->status;
                                        $payment_status = isset($registration->payment_status) ? (int) $registration->payment_status : 0;
                                        $participant_count = max(1, (int) $registration->participant_count);
                                        $total_amount = (float) $registration->fee * $participant_count;
                                        $member_name = trim($registration->title_name.$registration->first_name.' '.$registration->last_name);
                                        ?>
                                        <tr>
                                            <td>
                                                <strong class="d-block"><?= html_escape($registration->registration_code); ?></strong>
                                                <span class="small text-secondary"><?= !empty($registration->created_at) ? html_escape(date('Y-m-d H:i', strtotime($registration->created_at))) : '-'; ?></span>
                                            </td>
                                            <td>
                                                <strong class="d-block"><?= html_escape($member_name !== '' ? $member_name : '-'); ?></strong>
                                                <span class="small text-secondary"><?= html_escape($registration->email ?: '-'); ?></span>
                                            </td>
                                            <td>
                                                <strong class="d-block"><?= html_escape($registration->course_title); ?></strong>
                                                <span class="small text-secondary"><?= html_escape($registration->batch_no ?: '-'); ?></span>
                                            </td>
                                            <td><?= number_format($participant_count); ?> คน</td>
                                            <td>
                                                <strong class="d-block"><?= html_escape($format_money($total_amount)); ?></strong>
                                                <span class="small text-secondary">อัปโหลดแล้ว <?= html_escape($format_money($registration->paid_amount)); ?></span>
                                            </td>
                                            <td>
                                                <span class="badge <?= isset($registration_badges[$status]) ? $registration_badges[$status] : 'text-bg-secondary'; ?>">
                                                    <?= html_escape(isset($registration_labels[$status]) ? $registration_labels[$status] : $status); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge <?= isset($payment_badges[$payment_status]) ? $payment_badges[$payment_status] : 'text-bg-secondary'; ?>">
                                                    <?= html_escape(isset($payment_labels[$payment_status]) ? $payment_labels[$payment_status] : 'ไม่มีรายการ'); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a class="btn btn-sm btn-outline-primary" href="<?= site_url('admin/registrations/view/'.$registration->id); ?>">รายละเอียด</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </article>
            </main>

<script>
(function () {
    var courseSelect = document.getElementById('course_id');
    var batchSelect = document.getElementById('batch_id');

    if (!courseSelect || !batchSelect) {
        return;
    }

    courseSelect.addEventListener('change', function () {
        batchSelect.disabled = true;
        batchSelect.value = '0';
        courseSelect.form.submit();
    });
}());
</script>

<?php $this->load->view('admins/layouts/footer'); ?>
