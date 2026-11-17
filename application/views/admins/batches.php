<?php
$this->load->view('admins/layouts/header');
$this->load->view('admins/layouts/sidebar');

$batches = isset($batches) ? $batches : array();
$courses = isset($courses) ? $courses : array();
$edit_batch = isset($edit_batch) && is_object($edit_batch) ? $edit_batch : (object) array(
    'id' => NULL,
    'course_id' => '',
    'batch_no' => '',
    'start_date' => '',
    'end_date' => '',
    'start_time' => '',
    'end_time' => '',
    'registration_start' => '',
    'registration_end' => '',
    'capacity' => 0,
    'status' => 1
);
$stats = array_merge(
    array(
        'total' => 0,
        'open' => 0,
        'closed' => 0,
        'additional_open' => 0,
        'cancelled' => 0
    ),
    isset($stats) && is_array($stats) ? $stats : array()
);

$is_edit = !empty($edit_batch->id);
$form_action = $is_edit
    ? site_url('admin/batches/update/'.$edit_batch->id)
    : site_url('admin/batches/store');
$status_labels = array(
    1 => 'เปิดรับสมัคร',
    2 => 'ปิดรับสมัคร',
    3 => 'เปิดรับเพิ่ม',
    4 => 'ยกเลิก'
);
$status_classes = array(
    1 => 'text-bg-success',
    2 => 'text-bg-secondary',
    3 => 'text-bg-warning',
    4 => 'text-bg-danger'
);
$start_time_value = !empty($edit_batch->start_time) ? date('H:i', strtotime($edit_batch->start_time)) : '';
$end_time_value = !empty($edit_batch->end_time) ? date('H:i', strtotime($edit_batch->end_time)) : '';
$registration_start_value = !empty($edit_batch->registration_start) ? date('Y-m-d\TH:i', strtotime($edit_batch->registration_start)) : '';
$registration_end_value = !empty($edit_batch->registration_end) ? date('Y-m-d\TH:i', strtotime($edit_batch->registration_end)) : '';
$thai_months = array(1=>'มกราคม',2=>'กุมภาพันธ์',3=>'มีนาคม',4=>'เมษายน',5=>'พฤษภาคม',6=>'มิถุนายน',7=>'กรกฎาคม',8=>'สิงหาคม',9=>'กันยายน',10=>'ตุลาคม',11=>'พฤศจิกายน',12=>'ธันวาคม');
$thai_date = function($value) use ($thai_months) {
    if(empty($value) || !($time=strtotime($value))) return '-';
    return (int)date('j',$time).' '.$thai_months[(int)date('n',$time)].' '.((int)date('Y',$time)+543);
};
$thai_datetime = function($value) use ($thai_date) {
    if(empty($value) || !($time=strtotime($value))) return '-';
    return $thai_date($value).' เวลา '.date('H:i',$time).' น.';
};
?>
        <style>
            #batchFormModal .batch-form-section { padding: .9rem; border: 1px solid #e8edf4; border-radius: .8rem; background: #fff; }
            #batchFormModal .batch-form-section__title { display: flex; align-items: center; gap: .55rem; margin: 0 0 .8rem; color: #24456d; font-size: .88rem; font-weight: 700; }
            #batchFormModal .batch-form-section__number { display: inline-grid; width: 1.45rem; height: 1.45rem; place-items: center; border-radius: 50%; background: #e9f2ff; color: #1762b1; font-size: .72rem; }
            #batchFormModal .modal-content { border: 0; border-radius: 1rem; box-shadow: 0 1rem 3rem rgba(20, 39, 67, .2); overflow: hidden; }
            #batchFormModal .modal-header { padding: 1rem 1.25rem; border-bottom: 1px solid #e8edf4; background: linear-gradient(120deg, #eef5ff, #f8faff) !important; }
            #batchFormModal .modal-title { font-size: 1.05rem; font-weight: 700; color: #173b68; }
            #batchFormModal .modal-body { padding: 1.1rem 1.25rem; font-size: .875rem; }
            #batchFormModal .form-label { display: block; font-size: .8rem; color: #34445a; }
            #batchFormModal .form-text, #batchFormModal .js-thai-summary { font-size: .72rem; }
            #batchFormModal .form-control-sm, #batchFormModal .form-select-sm,
            #batchFormModal .form-control, #batchFormModal .form-select { min-height: 2.35rem; font-size: .84rem; border-color: #d7dfeb; border-radius: .55rem; }
            #batchFormModal .form-control:focus, #batchFormModal .form-select:focus { border-color: #80aee8; box-shadow: 0 0 0 .2rem rgba(13, 110, 253, .12); }
            #batchFormModal .batch-date-picker { --bs-gutter-x: .4rem; --bs-gutter-y: .35rem; }
            #batchFormModal .js-thai-summary { min-height: 1rem; margin-top: .3rem; color: #53749c !important; }
            #batchFormModal .modal-footer { padding: .8rem 1.25rem; border-top: 1px solid #e8edf4; background: #f8faff; }
            #batchFormModal .modal-footer .btn { min-width: 6.5rem; border-radius: .55rem; }
            @media (max-width: 575.98px) {
                #batchFormModal .modal-dialog { margin: .5rem; }
                #batchFormModal .modal-header, #batchFormModal .modal-body { padding-right: 1rem; padding-left: 1rem; }
                #batchFormModal .batch-form-section { padding: .75rem; }
                #batchFormModal .modal-footer { padding-right: 1rem; padding-left: 1rem; }
            }
        </style>
        <style>
            .admin-batches-page { --batch-ink: #26364b; --batch-muted: #718096; --batch-line: #e7edf4; color: var(--batch-ink); font-size: .9rem; }
            .admin-batches-page .admin-topbar h1 { font-size: 1.25rem; font-weight: 700; letter-spacing: -.01em; }
            .admin-batches-page .admin-topbar p { font-size: .82rem; }
            .admin-batches-page .admin-main { padding-top: 1.25rem !important; }
            .admin-batches-page .admin-stat-card { border-radius: .8rem; box-shadow: 0 .25rem .9rem rgba(32, 52, 77, .06) !important; }
            .admin-batches-page .admin-stat-card .card-body { padding: .95rem 1rem; }
            .admin-batches-page .admin-stat-card strong { margin: .35rem 0 !important; font-size: 1.65rem !important; color: #263e5d; }
            .admin-batches-page .admin-stat-card span, .admin-batches-page .admin-stat-card p { font-size: .76rem !important; }
            .admin-batches-page .card { border-radius: .8rem; }
            .admin-batches-page .card-header { padding: .85rem 1rem; border-bottom-color: var(--batch-line); }
            .admin-batches-page .card-header h3 { font-size: .98rem; font-weight: 700; color: #263e5d; }
            .admin-batches-page .admin-batches-table { font-size: .8rem; color: var(--batch-ink); }
            .admin-batches-page .admin-batches-table thead th { padding: .7rem .65rem; white-space: nowrap; background: #f6f8fb; color: #52647b; font-size: .72rem; font-weight: 700; border-bottom-color: var(--batch-line); }
            .admin-batches-page .admin-batches-table tbody td { padding: .7rem .65rem; line-height: 1.5; vertical-align: middle; border-color: #edf1f5; }
            .admin-batches-page .admin-batches-table tbody tr:hover { background: #f8fbff; }
            .admin-batches-page .admin-batches-table .badge { padding: .35em .55em; font-size: .68rem; font-weight: 600; }
            .admin-batches-page .admin-batches-table .btn { font-size: .72rem; white-space: nowrap; }
            .admin-batches-page .admin-batches-table td:last-child > div { gap: .35rem !important; }
            .admin-batches-page .alert { border: 0; border-radius: .7rem; font-size: .84rem; }
            @media (max-width: 767.98px) {
                .admin-batches-page .admin-main { padding-right: .75rem; padding-left: .75rem; }
                .admin-batches-page .admin-stat-card .card-body { padding: .8rem; }
                .admin-batches-page .admin-batches-table tbody td { padding: .6rem .55rem; }
            }
        </style>
        <div class="admin-content">
            <header class="admin-topbar d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 px-3 px-lg-4 py-3 border-bottom">
                <div>
                    <h1 class="h4 mb-1">รุ่นอบรม</h1>
                    <p class="mb-0 text-secondary">จัดการรอบและกำหนดการอบรมของแต่ละหลักสูตร</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <?php if ($is_edit): ?>
                        <a class="btn btn-outline-dark" href="<?= site_url('admin/batches'); ?>">ยกเลิกแก้ไข</a>
                    <?php endif; ?>
                    <a class="btn btn-dark" href="<?= site_url('admin/logout'); ?>">ออกจากระบบ</a>
                </div>
            </header>

            <main class="admin-main py-4 admin-batches-page">
                <?php if ($this->session->flashdata('success')): ?>
                    <div class="alert alert-success fw-semibold" role="status"><?= html_escape($this->session->flashdata('success')); ?></div>
                <?php endif; ?>

                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger fw-semibold" role="alert"><?= html_escape($this->session->flashdata('error')); ?></div>
                <?php endif; ?>

                <?php if (empty($courses)): ?>
                    <div class="alert alert-warning fw-semibold" role="alert">
                        กรุณาเพิ่มหลักสูตรก่อนเพิ่มข้อมูลรุ่นอบรม
                    </div>
                <?php endif; ?>

                <section class="row row-cols-1 row-cols-md-2 row-cols-xl-5 g-3 mb-4" aria-label="สรุปรุ่นอบรม">
                    <div class="col">
                        <article class="admin-stat-card card h-100 border-0 shadow-sm position-relative">
                            <div class="card-body">
                                <span class="text-secondary small fw-bold">รุ่นทั้งหมด</span>
                                <strong class="d-block fs-2 lh-1 my-2"><?= number_format((int) $stats['total']); ?></strong>
                                <p class="mb-0 text-secondary small">รายการในระบบ</p>
                            </div>
                        </article>
                    </div>
                    <div class="col">
                        <article class="admin-stat-card stat-green card h-100 border-0 shadow-sm position-relative">
                            <div class="card-body">
                                <span class="text-secondary small fw-bold">เปิดรับสมัคร</span>
                                <strong class="d-block fs-2 lh-1 my-2"><?= number_format((int) $stats['open']); ?></strong>
                                <p class="mb-0 text-secondary small">พร้อมลงทะเบียน</p>
                            </div>
                        </article>
                    </div>
                    <div class="col">
                        <article class="admin-stat-card stat-blue card h-100 border-0 shadow-sm position-relative">
                            <div class="card-body">
                                <span class="text-secondary small fw-bold">ปิดรับสมัคร</span>
                                <strong class="d-block fs-2 lh-1 my-2"><?= number_format((int) $stats['closed']); ?></strong>
                                <p class="mb-0 text-secondary small">สิ้นสุดการรับสมัคร</p>
                            </div>
                        </article>
                    </div>
                    <div class="col">
                        <article class="admin-stat-card stat-yellow card h-100 border-0 shadow-sm position-relative">
                            <div class="card-body">
                                <span class="text-secondary small fw-bold">เปิดรับเพิ่ม</span>
                                <strong class="d-block fs-2 lh-1 my-2"><?= number_format((int) $stats['additional_open']); ?></strong>
                                <p class="mb-0 text-secondary small">เปิดรับสมัครเพิ่มเติม</p>
                            </div>
                        </article>
                    </div>
                    <div class="col">
                        <article class="admin-stat-card stat-red card h-100 border-0 shadow-sm position-relative">
                            <div class="card-body">
                                <span class="text-secondary small fw-bold">ยกเลิก</span>
                                <strong class="d-block fs-2 lh-1 my-2"><?= number_format((int) $stats['cancelled']); ?></strong>
                                <p class="mb-0 text-secondary small">รุ่นที่ยกเลิก</p>
                            </div>
                        </article>
                    </div>
                </section>

                <section class="row g-4">
                    <div class="col-12">
                        <article class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white d-flex align-items-center justify-content-between gap-3">
                                <h3 class="h5 mb-0">รายการรุ่นอบรม</h3>
                                <div class="d-flex flex-wrap gap-2">
                                    <a class="btn btn-outline-primary btn-sm fw-normal" href="<?= site_url('admin/batches'); ?>">รีเฟรช</a>
                                    <button class="btn btn-primary btn-sm fw-normal" type="button" data-bs-toggle="modal" data-bs-target="#batchFormModal" <?= empty($courses) ? 'disabled' : ''; ?>>
                                        เพิ่มรุ่นอบรม
                                    </button>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0 admin-batches-table">
                                        <thead>
                                            <tr>
                                                <th>รุ่น</th>
                                                <th>หลักสูตร</th>
                                                <th>วันอบรม</th>
                                                <th>รับสมัคร</th>
                                                <th>รับ</th>
                                                <th>จำนวนผู้อบรม</th>
                                                <th>สถานะ</th>
                                                <th>หลังอบรม</th>
                                                <th>จัดการ</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($batches)): ?>
                                                <tr>
                                                    <td colspan="9" class="text-center text-secondary py-4">ยังไม่มีข้อมูลรุ่นอบรม</td>
                                                </tr>
                                            <?php endif; ?>

                                            <?php foreach ($batches as $batch): ?>
                                                <?php $batch_status = (int) $batch->status; ?>
                                                <tr>
                                                    <td><?= html_escape($batch->batch_no ?: '-'); ?></td>
                                                    <td>
                                                        <strong class="d-block"><?= html_escape($batch->course_title ?: '-'); ?></strong>
                                                        <span class="small text-secondary"><?= html_escape($batch->category_name ?: ''); ?></span>
                                                    </td>
                                                    <td>
                                                        <?= html_escape($thai_date($batch->start_date)); ?>
                                                        <?php if (!empty($batch->end_date) && $batch->end_date !== $batch->start_date): ?>
                                                            <br><span class="text-secondary">ถึง</span> <?= html_escape($thai_date($batch->end_date)); ?>
                                                        <?php endif; ?>
                                                        <br><span class="small text-secondary">เวลา <?= !empty($batch->start_time) ? html_escape(date('H:i', strtotime($batch->start_time))) : '-'; ?><?php if (!empty($batch->end_time)): ?>–<?= html_escape(date('H:i', strtotime($batch->end_time))); ?><?php endif; ?> น.</span>
                                                    </td>
                                                    <td>
                                                        <?= html_escape($thai_datetime($batch->registration_start)); ?>
                                                        <?php if (!empty($batch->registration_end)): ?>
                                                            <br><span class="small text-secondary">ถึง <?= html_escape($thai_datetime($batch->registration_end)); ?></span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?= number_format((int) $batch->capacity); ?></td>
                                                    <td><?= number_format((int) $batch->participant_count); ?> คน</td>
                                                    <td>
                                                        <span class="badge <?= isset($status_classes[$batch_status]) ? $status_classes[$batch_status] : 'text-bg-secondary'; ?>">
                                                            <?= html_escape(isset($status_labels[$batch_status]) ? $status_labels[$batch_status] : $batch->status); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?php if (empty($batch->evaluation_id)): ?>
                                                            <span class="badge text-bg-secondary">ยังไม่ตั้งค่าแบบประเมิน</span>
                                                        <?php elseif ((int) $batch->evaluation_status !== 1): ?>
                                                            <span class="badge text-bg-dark">แบบประเมินปิดแล้ว</span>
                                                        <?php elseif (time() < strtotime($batch->evaluation_open_at)): ?>
                                                            <span class="badge text-bg-info">แบบประเมินรอเปิด</span>
                                                        <?php elseif (time() > strtotime($batch->evaluation_close_at)): ?>
                                                            <span class="badge text-bg-secondary">แบบประเมินหมดเวลา</span>
                                                        <?php else: ?>
                                                            <span class="badge text-bg-success">แบบประเมินเปิดอยู่</span>
                                                        <?php endif; ?>
                                                        <br><?php if ((int)$batch->certificate_template_count > 0): ?><span class="badge text-bg-primary mt-1">วุฒิบัตรพร้อม · <?= number_format((int)$batch->certificate_issued_count) ?> ใบ</span><?php else: ?><span class="badge text-bg-warning mt-1">ยังไม่มีแม่แบบวุฒิบัตร</span><?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex flex-wrap gap-2">
                                                            <a class="btn btn-sm btn-outline-dark" href="<?= site_url('admin/registrations?course_id='.(int) $batch->course_id.'&batch_id='.(int) $batch->id); ?>">ผู้ลงทะเบียน</a>
                                                            <a class="btn btn-sm btn-outline-primary" href="<?= site_url('admin/batches?edit='.$batch->id); ?>">แก้ไข</a>
                                                            <a class="btn btn-sm btn-outline-success" href="<?= site_url('admin/batches/'.$batch->id.'/evaluation'); ?>">แบบประเมิน</a>
                                                            <a class="btn btn-sm btn-outline-info" href="<?= site_url('admin/certificates?batch_id='.$batch->id); ?>">วุฒิบัตร</a>
                                                            <?php if (admin_can_delete($this->session)): ?>
                                                            <form class="js-batch-delete-form" method="post" action="<?= site_url('admin/batches/delete/'.$batch->id); ?>" data-batch-title="<?= html_escape(($batch->batch_no ?: 'รุ่นอบรม').' - '.($batch->course_title ?: '')); ?>">
                                                                <button class="btn btn-sm btn-outline-danger" type="submit">ลบ</button>
                                                            </form>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </article>
                    </div>

                </section>

                <div class="modal fade" id="batchFormModal" tabindex="-1" aria-labelledby="batchFormModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable batch-form-modal">
                        <div class="modal-content">
                            <form method="post" action="<?= $form_action; ?>">
                                <div class="modal-header bg-primary bg-opacity-10">
                                    <h2 class="modal-title h5" id="batchFormModalLabel"><?= $is_edit ? 'แก้ไขรุ่นอบรม' : 'เพิ่มรุ่นอบรม'; ?></h2>
                                    <a class="btn-close" href="<?= site_url('admin/batches'); ?>" aria-label="Close"></a>
                                </div>

                                <div class="modal-body py-3">
                                    <div class="row g-2">
                                        <div class="col-12">
                                            <label class="form-label fw-bold small mb-1" for="course_id">หลักสูตร</label>
                                            <select class="form-select form-select-sm" id="course_id" name="course_id" required>
                                                <option value="">เลือกหลักสูตร</option>
                                                <?php foreach ($courses as $course): ?>
                                                    <option value="<?= (int) $course->id; ?>" <?= (int) $edit_batch->course_id === (int) $course->id ? 'selected' : ''; ?>>
                                                        <?= html_escape($course->title); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-bold small mb-1" for="batch_no">รุ่นอบรม</label>
                                            <input class="form-control form-control-sm" id="batch_no" type="text" name="batch_no" value="<?= $is_edit ? html_escape($edit_batch->batch_no) : ''; ?>" placeholder="เช่น รุ่นที่ 1">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-bold small mb-1" for="capacity">จำนวนรับ</label>
                                            <input class="form-control form-control-sm" id="capacity" type="number" name="capacity" value="<?= (int) $edit_batch->capacity; ?>" min="0">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-bold small mb-1" for="start_date">วันที่เริ่มอบรม</label>
                                            <input id="start_date" type="hidden" name="start_date" class="js-thai-picker" data-with-time="0" value="<?= $is_edit ? html_escape($edit_batch->start_date) : ''; ?>">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-bold small mb-1" for="end_date">วันที่สิ้นสุดอบรม</label>
                                            <input id="end_date" type="hidden" name="end_date" class="js-thai-picker" data-with-time="0" value="<?= $is_edit ? html_escape($edit_batch->end_date) : ''; ?>">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-bold small mb-1" for="start_time">เวลาเริ่ม</label>
                                            <input class="form-control form-control-sm" id="start_time" type="time" name="start_time" value="<?= html_escape($start_time_value); ?>">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-bold small mb-1" for="end_time">เวลาสิ้นสุด</label>
                                            <input class="form-control form-control-sm" id="end_time" type="time" name="end_time" value="<?= html_escape($end_time_value); ?>">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-bold small mb-1" for="registration_start">เปิดรับสมัคร</label>
                                            <input id="registration_start" type="hidden" name="registration_start" class="js-thai-picker" data-with-time="1" value="<?= html_escape($registration_start_value); ?>">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-bold small mb-1" for="registration_end">ปิดรับสมัคร</label>
                                            <input id="registration_end" type="hidden" name="registration_end" class="js-thai-picker" data-with-time="1" value="<?= html_escape($registration_end_value); ?>">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-bold small mb-1" for="status">สถานะ</label>
                                            <select class="form-select" id="status" name="status">
                                                <?php foreach ($status_labels as $value => $label): ?>
                                                    <option value="<?= (int) $value; ?>" <?= (int) $edit_batch->status === (int) $value ? 'selected' : ''; ?>>
                                                        <?= html_escape($label); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        </div>
                                    </section>
                                </div>

                                <div class="modal-footer py-2">
                                    <a class="btn btn-outline-dark" href="<?= site_url('admin/batches'); ?>">ยกเลิก</a>
                                    <button class="btn btn-primary" type="submit" <?= empty($courses) ? 'disabled' : ''; ?>>
                                        <?= $is_edit ? 'บันทึกการแก้ไข' : 'เพิ่มรุ่นอบรม'; ?>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <script>
                    (function () {
                        var months = ['มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน','กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'];
                        function addOption(select, value, text) { var option = document.createElement('option'); option.value = value; option.textContent = text; select.appendChild(option); }
                        function pad(value) { return String(value).padStart(2, '0'); }
                        document.querySelectorAll('.js-thai-picker').forEach(function (hidden) {
                            var withTime = hidden.dataset.withTime === '1';
                            var box = document.createElement('div'); box.className = 'row g-1 batch-date-picker';
                            box.innerHTML = '<div class="col-3 col-sm-2"><select class="form-select form-select-sm js-day" aria-label="วัน"></select></div><div class="col-9 col-sm-4"><select class="form-select form-select-sm js-month" aria-label="เดือน"></select></div><div class="col-7 col-sm-3"><select class="form-select form-select-sm js-year" aria-label="ปี พ.ศ."></select></div>' + (withTime ? '<div class="col-5 col-sm-3"><input class="form-control form-control-sm js-time" type="time" step="60" aria-label="เวลา"></div>' : '');
                            var label = document.createElement('div'); label.className = 'form-text text-primary js-thai-summary';
                            hidden.insertAdjacentElement('afterend', box); box.insertAdjacentElement('afterend', label);
                            var day=box.querySelector('.js-day'),month=box.querySelector('.js-month'),year=box.querySelector('.js-year'),time=box.querySelector('.js-time');
                            addOption(day,'','วัน'); addOption(month,'','เดือน'); addOption(year,'','ปี พ.ศ.');
                            months.forEach(function(name,index){addOption(month,index+1,name)});
                            var currentBuddhistYear = new Date().getFullYear() + 543;
                            for(var buddhistYear=currentBuddhistYear-2;buddhistYear<=currentBuddhistYear+1;buddhistYear++) addOption(year,buddhistYear,buddhistYear);
                            var match=(hidden.value||'').match(/^(\d{4})-(\d{2})-(\d{2})(?:T(\d{2}):(\d{2}))?/);
                            if(match){month.value=Number(match[2]);year.value=Number(match[1])+543;if(time)time.value=(match[4]||'00')+':'+(match[5]||'00')}
                            function fillDays(selected){var old=selected||day.value,count=(month.value&&year.value)?new Date(Number(year.value)-543,Number(month.value),0).getDate():31;day.innerHTML='';addOption(day,'','วัน');for(var value=1;value<=count;value++)addOption(day,value,value);if(old&&Number(old)<=count)day.value=Number(old)}
                            function update(){fillDays();if(!day.value||!month.value||!year.value||(withTime&&!time.value)){hidden.value='';label.textContent='กรุณาเลือกวัน เดือน ปี'+(withTime?' และเวลา':'');return}hidden.value=(Number(year.value)-543)+'-'+pad(month.value)+'-'+pad(day.value)+(withTime?'T'+time.value:'');label.textContent=Number(day.value)+' '+months[Number(month.value)-1]+' '+year.value+(withTime?' เวลา '+time.value+' น.':'')}
                            fillDays(match?Number(match[3]):'');[day,month,year].forEach(function(item){item.addEventListener('change',update)});if(time)time.addEventListener('change',update);update();
                        });
                    })();
                </script>

                <?php if ($is_edit): ?>
                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            var modal = new bootstrap.Modal(document.getElementById('batchFormModal'));
                            modal.show();
                        });
                    </script>
                <?php endif; ?>
                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                <script>
                    document.querySelectorAll('.js-batch-delete-form').forEach(function (form) {
                        form.addEventListener('submit', function (event) {
                            event.preventDefault();

                            Swal.fire({
                                title: 'ยืนยันการลบรุ่นอบรม?',
                                text: 'ต้องการลบ "' + form.dataset.batchTitle + '" ใช่หรือไม่',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonText: 'ลบข้อมูล',
                                cancelButtonText: 'ยกเลิก',
                                confirmButtonColor: '#d94f45',
                                reverseButtons: true
                            }).then(function (result) {
                                if (result.isConfirmed) {
                                    form.submit();
                                }
                            });
                        });
                    });
                </script>
            </main>
<?php $this->load->view('admins/layouts/footer'); ?>
