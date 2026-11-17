<?php
$this->load->view('admins/layouts/header');
$this->load->view('admins/layouts/sidebar');

$courses = isset($courses) ? $courses : array();
$categories = isset($categories) ? $categories : array();
$stats = array_merge(
    array(
        'total' => 0,
        'draft' => 0,
        'open' => 0,
        'closed' => 0,
        'featured' => 0
    ),
    isset($stats) && is_array($stats) ? $stats : array()
);

$status_labels = array(
    1 => 'ร่าง',
    2 => 'เปิด',
    3 => 'ปิด'
);
$status_classes = array(
    1 => 'text-bg-secondary',
    2 => 'text-bg-success',
    3 => 'text-bg-danger'
);
$type_labels = array(
    '' => 'ไม่ระบุ',
    'online' => 'Online',
    'onsite' => 'Onsite',
    'hybrid' => 'Hybrid'
);
?>
        <style>
            .admin-courses-page { --course-ink: #26364b; --course-line: #e7edf4; color: var(--course-ink); font-size: .9rem; }
            .admin-courses-page .admin-topbar h1 { font-size: 1.25rem; font-weight: 700; }
            .admin-courses-page .admin-topbar p { font-size: .82rem; }
            .admin-courses-page .admin-main { padding-top: 1.25rem !important; }
            .admin-courses-page .admin-stat-card { border-radius: .8rem; box-shadow: 0 .25rem .9rem rgba(32, 52, 77, .06) !important; }
            .admin-courses-page .admin-stat-card .card-body { padding: .95rem 1rem; }
            .admin-courses-page .admin-stat-card strong { margin: .35rem 0 !important; font-size: 1.65rem !important; color: #263e5d; }
            .admin-courses-page .admin-stat-card span, .admin-courses-page .admin-stat-card p { font-size: .76rem !important; }
            .admin-courses-page .card { border-radius: .8rem; }
            .admin-courses-page .card-header { padding: .85rem 1rem; border-bottom-color: var(--course-line); }
            .admin-courses-page .card-header h3 { font-size: .98rem; font-weight: 700; }
            .admin-courses-page .table { font-size: .8rem; color: var(--course-ink); }
            .admin-courses-page .table thead th { padding: .7rem .65rem; white-space: nowrap; background: #f6f8fb; color: #52647b; font-size: .72rem; font-weight: 700; border-bottom-color: var(--course-line); }
            .admin-courses-page .table tbody td { padding: .7rem .65rem; line-height: 1.5; vertical-align: middle; border-color: #edf1f5; }
            .admin-courses-page .table tbody tr:hover { background: #f8fbff; }
            .admin-courses-page .table tbody td strong { color: #455468; font-weight: 500; }
            .admin-courses-page .table tbody td code { color: #78879a; font-size: .72rem; }
            .admin-courses-page .table .badge { padding: .4em .6em; font-size: .68rem; font-weight: 600; }
            .admin-courses-page .table .btn { font-size: .72rem; white-space: nowrap; }
            .admin-courses-page .table td:last-child > div { gap: .35rem !important; }
            .admin-courses-page .alert { border: 0; border-radius: .7rem; font-size: .84rem; }
            @media (max-width: 767.98px) {
                .admin-courses-page .admin-main { padding-right: .75rem; padding-left: .75rem; }
                .admin-courses-page .admin-stat-card .card-body { padding: .8rem; }
                .admin-courses-page .table tbody td { padding: .6rem .55rem; }
                .admin-courses-page .card-header { align-items: flex-start !important; flex-direction: column; }
            }
        </style>
        <div class="admin-content">
            <header class="admin-topbar d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 px-3 px-lg-4 py-3 border-bottom">
                <div>
                    <h1 class="h4 mb-1">หลักสูตร</h1>
                    <p class="mb-0 text-secondary">จัดการข้อมูลหลักสูตรอบรมและเชื่อมโยงกับหมวดหมู่หลักสูตร</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a class="btn btn-dark" href="<?= site_url('admin/logout'); ?>">ออกจากระบบ</a>
                </div>
            </header>

            <main class="admin-main py-4 admin-courses-page">
                <?php if ($this->session->flashdata('success')): ?>
                    <div class="alert alert-success fw-semibold" role="status"><?= html_escape($this->session->flashdata('success')); ?></div>
                <?php endif; ?>

                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger fw-semibold" role="alert"><?= html_escape($this->session->flashdata('error')); ?></div>
                <?php endif; ?>

                <?php if (empty($categories)): ?>
                    <div class="alert alert-warning fw-semibold" role="alert">
                        กรุณาเพิ่มหมวดหมู่หลักสูตรก่อนเพิ่มข้อมูลหลักสูตร
                    </div>
                <?php endif; ?>

                <section class="row row-cols-1 row-cols-md-2 row-cols-xl-4 g-3 mb-4" aria-label="สรุปหลักสูตร">
                    <div class="col">
                        <article class="admin-stat-card card h-100 border-0 shadow-sm position-relative">
                            <div class="card-body">
                                <span class="text-secondary small fw-bold">หลักสูตรทั้งหมด</span>
                                <strong class="d-block fs-2 lh-1 my-2"><?= number_format((int) $stats['total']); ?></strong>
                                <p class="mb-0 text-secondary small">รายการในระบบ</p>
                            </div>
                        </article>
                    </div>
                    <div class="col">
                        <article class="admin-stat-card stat-green card h-100 border-0 shadow-sm position-relative">
                            <div class="card-body">
                                <span class="text-secondary small fw-bold">เปิดใช้งาน</span>
                                <strong class="d-block fs-2 lh-1 my-2"><?= number_format((int) $stats['open']); ?></strong>
                                <p class="mb-0 text-secondary small">พร้อมแสดงผล</p>
                            </div>
                        </article>
                    </div>
                    <div class="col">
                        <article class="admin-stat-card stat-blue card h-100 border-0 shadow-sm position-relative">
                            <div class="card-body">
                                <span class="text-secondary small fw-bold">ร่าง</span>
                                <strong class="d-block fs-2 lh-1 my-2"><?= number_format((int) $stats['draft']); ?></strong>
                                <p class="mb-0 text-secondary small">ยังไม่เผยแพร่</p>
                            </div>
                        </article>
                    </div>
                    <div class="col">
                        <article class="admin-stat-card stat-red card h-100 border-0 shadow-sm position-relative">
                            <div class="card-body">
                                <span class="text-secondary small fw-bold">แนะนำหน้าแรก</span>
                                <strong class="d-block fs-2 lh-1 my-2"><?= number_format((int) $stats['featured']); ?></strong>
                                <p class="mb-0 text-secondary small">หลักสูตรเด่น</p>
                            </div>
                        </article>
                    </div>
                </section>

                <section class="row g-4">

                    <div class="col-12">
                        <article class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-primary text-white d-flex align-items-center justify-content-between gap-3">
                                <h3 class="h5 mb-0">รายการหลักสูตร</h3>
                                <div class="d-flex flex-wrap gap-2">
                                    <a class="btn btn-light btn-sm text-primary" href="<?= site_url('admin/categories'); ?>">หมวดหมู่หลักสูตร</a>
                                    <a class="btn btn-light btn-sm text-primary" href="<?= site_url('admin/courses'); ?>">รีเฟรช</a>
                                    <a class="btn btn-warning btn-sm text-dark" href="<?= site_url('admin/courses/create'); ?>">เพิ่มหลักสูตร</a>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>หลักสูตร</th>
                                                <th>หมวดหมู่</th>
                                                <th>รูปแบบ</th>
                                                <th>รับ</th>
                                                <th>ค่าสมัคร</th>
                                                <th>สถานะ</th>
                                                <th>จัดการ</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($courses)): ?>
                                                <tr>
                                                    <td colspan="7" class="text-center text-secondary py-4">ยังไม่มีข้อมูลหลักสูตร</td>
                                                </tr>
                                            <?php endif; ?>

                                            <?php foreach ($courses as $course): ?>
                                                <?php $course_status = (int) $course->status; ?>
                                                <tr>
                                                    <td>
                                                        <strong class="d-block"><?= html_escape($course->title); ?></strong>
                                                        <code class="small"><?= html_escape($course->slug); ?></code>
                                                        <?php if ((int) $course->is_featured === 1): ?>
                                                            <span class="badge text-bg-warning ms-1">แนะนำ</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?= html_escape($course->category_name ?: '-'); ?></td>
                                                    <td><?= html_escape(isset($type_labels[$course->training_type]) ? $type_labels[$course->training_type] : $course->training_type); ?></td>
                                                    <td><?= number_format((int) $course->capacity); ?></td>
                                                    <td><?= isset($course->fee_mode) && $course->fee_mode==='options'?'เริ่มต้น ':'' ?><?= number_format((float) $course->fee, 2); ?></td>
                                                    <td>
                                                        <span class="badge <?= isset($status_classes[$course_status]) ? $status_classes[$course_status] : 'text-bg-secondary'; ?>">
                                                            <?= html_escape(isset($status_labels[$course_status]) ? $status_labels[$course_status] : $course->status); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex flex-wrap gap-2">
                                                            <a class="btn btn-sm btn-outline-primary" href="<?= site_url('admin/courses/edit/'.$course->id); ?>">แก้ไข</a>
                                                            <a class="btn btn-sm btn-outline-info" href="<?= site_url('admin/course-details/'.$course->id); ?>">รายละเอียด</a>
                                                            <a class="btn btn-sm btn-outline-success" href="<?= site_url('admin/course-instructors/'.$course->id); ?>">วิทยากร</a>
                                                            <?php if (admin_can_delete($this->session)): ?>
                                                            <?php if ((int) $course->batch_count > 0): ?>
                                                                <button class="btn btn-sm btn-outline-danger" type="button" disabled title="มีรุ่นอบรม ไม่สามารถลบได้">ลบ</button>
                                                                <small class="text-secondary align-self-center">มีรุ่นอบรม <?= number_format((int) $course->batch_count); ?> รุ่น</small>
                                                            <?php else: ?>
                                                                <form class="js-course-delete-form" method="post" action="<?= site_url('admin/courses/delete/'.$course->id); ?>" data-course-title="<?= html_escape($course->title); ?>">
                                                                    <button class="btn btn-sm btn-outline-danger" type="submit">ลบ</button>
                                                                </form>
                                                            <?php endif; ?>
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

            </main>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            document.querySelectorAll('.js-course-delete-form').forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    event.preventDefault();

                    Swal.fire({
                        title: 'ยืนยันการลบหลักสูตร?',
                        text: 'ต้องการลบ "' + form.dataset.courseTitle + '" ใช่หรือไม่',
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
<?php $this->load->view('admins/layouts/footer'); ?>
