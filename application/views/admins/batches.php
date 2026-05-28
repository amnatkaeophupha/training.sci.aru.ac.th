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
?>
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

            <main class="admin-main py-4">
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
                                    <a class="btn btn-outline-primary btn-sm" href="<?= site_url('admin/batches'); ?>">รีเฟรช</a>
                                    <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#batchFormModal" <?= empty($courses) ? 'disabled' : ''; ?>>
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
                                                <th>เวลาอบรม</th>
                                                <th>รับสมัคร</th>
                                                <th>รับ</th>
                                                <th>สถานะ</th>
                                                <th>จัดการ</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($batches)): ?>
                                                <tr>
                                                    <td colspan="8" class="text-center text-secondary py-4">ยังไม่มีข้อมูลรุ่นอบรม</td>
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
                                                        <?= html_escape($batch->start_date ?: '-'); ?>
                                                        <?php if (!empty($batch->end_date) && $batch->end_date !== $batch->start_date): ?>
                                                            <span class="text-secondary">ถึง</span> <?= html_escape($batch->end_date); ?>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?= !empty($batch->start_time) ? html_escape(date('H:i', strtotime($batch->start_time))) : '-'; ?>
                                                        <?php if (!empty($batch->end_time)): ?>
                                                            <span class="text-secondary">-</span> <?= html_escape(date('H:i', strtotime($batch->end_time))); ?>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?= !empty($batch->registration_start) ? html_escape(date('Y-m-d H:i', strtotime($batch->registration_start))) : '-'; ?>
                                                        <?php if (!empty($batch->registration_end)): ?>
                                                            <br><span class="small text-secondary">ถึง <?= html_escape(date('Y-m-d H:i', strtotime($batch->registration_end))); ?></span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?= number_format((int) $batch->capacity); ?></td>
                                                    <td>
                                                        <span class="badge <?= isset($status_classes[$batch_status]) ? $status_classes[$batch_status] : 'text-bg-secondary'; ?>">
                                                            <?= html_escape(isset($status_labels[$batch_status]) ? $status_labels[$batch_status] : $batch->status); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex flex-wrap gap-2">
                                                            <a class="btn btn-sm btn-outline-primary" href="<?= site_url('admin/batches?edit='.$batch->id); ?>">แก้ไข</a>
                                                            <form class="js-batch-delete-form" method="post" action="<?= site_url('admin/batches/delete/'.$batch->id); ?>" data-batch-title="<?= html_escape(($batch->batch_no ?: 'รุ่นอบรม').' - '.($batch->course_title ?: '')); ?>">
                                                                <button class="btn btn-sm btn-outline-danger" type="submit">ลบ</button>
                                                            </form>
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
                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                        <div class="modal-content">
                            <form method="post" action="<?= $form_action; ?>">
                                <div class="modal-header bg-primary bg-opacity-10">
                                    <h2 class="modal-title h5" id="batchFormModalLabel"><?= $is_edit ? 'แก้ไขรุ่นอบรม' : 'เพิ่มรุ่นอบรม'; ?></h2>
                                    <a class="btn-close" href="<?= site_url('admin/batches'); ?>" aria-label="Close"></a>
                                </div>

                                <div class="modal-body">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label fw-bold" for="course_id">หลักสูตร</label>
                                            <select class="form-select" id="course_id" name="course_id" required>
                                                <option value="">เลือกหลักสูตร</option>
                                                <?php foreach ($courses as $course): ?>
                                                    <option value="<?= (int) $course->id; ?>" <?= (int) $edit_batch->course_id === (int) $course->id ? 'selected' : ''; ?>>
                                                        <?= html_escape($course->title); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-bold" for="batch_no">รุ่นอบรม</label>
                                            <input class="form-control" id="batch_no" type="text" name="batch_no" value="<?= $is_edit ? html_escape($edit_batch->batch_no) : ''; ?>" placeholder="เช่น รุ่นที่ 1">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-bold" for="capacity">จำนวนรับ</label>
                                            <input class="form-control" id="capacity" type="number" name="capacity" value="<?= (int) $edit_batch->capacity; ?>" min="0">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-bold" for="start_date">วันที่เริ่มอบรม</label>
                                            <input class="form-control" id="start_date" type="date" name="start_date" value="<?= $is_edit ? html_escape($edit_batch->start_date) : ''; ?>">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-bold" for="end_date">วันที่สิ้นสุดอบรม</label>
                                            <input class="form-control" id="end_date" type="date" name="end_date" value="<?= $is_edit ? html_escape($edit_batch->end_date) : ''; ?>">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-bold" for="start_time">เวลาเริ่ม</label>
                                            <input class="form-control" id="start_time" type="time" name="start_time" value="<?= html_escape($start_time_value); ?>">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-bold" for="end_time">เวลาสิ้นสุด</label>
                                            <input class="form-control" id="end_time" type="time" name="end_time" value="<?= html_escape($end_time_value); ?>">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-bold" for="registration_start">เปิดรับสมัคร</label>
                                            <input class="form-control" id="registration_start" type="datetime-local" name="registration_start" value="<?= html_escape($registration_start_value); ?>">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-bold" for="registration_end">ปิดรับสมัคร</label>
                                            <input class="form-control" id="registration_end" type="datetime-local" name="registration_end" value="<?= html_escape($registration_end_value); ?>">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-bold" for="status">สถานะ</label>
                                            <select class="form-select" id="status" name="status">
                                                <?php foreach ($status_labels as $value => $label): ?>
                                                    <option value="<?= (int) $value; ?>" <?= (int) $edit_batch->status === (int) $value ? 'selected' : ''; ?>>
                                                        <?= html_escape($label); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="modal-footer">
                                    <a class="btn btn-outline-dark" href="<?= site_url('admin/batches'); ?>">ยกเลิก</a>
                                    <button class="btn btn-primary" type="submit" <?= empty($courses) ? 'disabled' : ''; ?>>
                                        <?= $is_edit ? 'บันทึกการแก้ไข' : 'เพิ่มรุ่นอบรม'; ?>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

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
