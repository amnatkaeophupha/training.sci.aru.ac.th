<?php
$this->load->view('admins/layouts/header');
$this->load->view('admins/layouts/sidebar');

$details = isset($details) ? $details : array();
$course = isset($course) && is_object($course) ? $course : (object) array(
    'id' => NULL,
    'title' => '',
    'slug' => ''
);
$edit_detail = isset($edit_detail) && is_object($edit_detail) ? $edit_detail : (object) array(
    'id' => NULL,
    'course_id' => $course->id,
    'section_type' => 'learning',
    'title' => '',
    'content' => '',
    'sort_order' => 0
);
$stats = array_merge(
    array(
        'total' => 0,
        'learning' => 0,
        'qualification' => 0,
        'document' => 0,
        'note' => 0
    ),
    isset($stats) && is_array($stats) ? $stats : array()
);

$is_edit = !empty($edit_detail->id);
$form_action = $is_edit
    ? site_url('admin/course-details/update/'.$edit_detail->id)
    : site_url('admin/course-details/store/'.$course->id);
$section_labels = array(
    'learning' => 'สิ่งที่จะได้เรียนรู้',
    'qualification' => 'คุณสมบัติผู้เข้าอบรม',
    'document' => 'เอกสารที่ต้องใช้',
    'note' => 'หมายเหตุ'
);
$section_classes = array(
    'learning' => 'text-bg-success',
    'qualification' => 'text-bg-primary',
    'document' => 'text-bg-warning',
    'note' => 'text-bg-secondary'
);
?>
        <div class="admin-content">
            <header class="admin-topbar d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 px-3 px-lg-4 py-3 border-bottom">
                <div>
                    <h1 class="h4 mb-1">รายละเอียดหลักสูตร</h1>
                    <p class="mb-0 text-secondary"><?= html_escape($course->title); ?></p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a class="btn btn-outline-dark" href="<?= site_url('admin/courses'); ?>">กลับไปหลักสูตร</a>
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

                <section class="row row-cols-1 row-cols-md-2 row-cols-xl-5 g-3 mb-4" aria-label="สรุปรายละเอียดหลักสูตร">
                    <div class="col">
                        <article class="admin-stat-card card h-100 border-0 shadow-sm position-relative">
                            <div class="card-body">
                                <span class="text-secondary small fw-bold">ทั้งหมด</span>
                                <strong class="d-block fs-2 lh-1 my-2"><?= number_format((int) $stats['total']); ?></strong>
                                <p class="mb-0 text-secondary small">รายการ</p>
                            </div>
                        </article>
                    </div>
                    <div class="col">
                        <article class="admin-stat-card stat-green card h-100 border-0 shadow-sm position-relative">
                            <div class="card-body">
                                <span class="text-secondary small fw-bold">เรียนรู้</span>
                                <strong class="d-block fs-2 lh-1 my-2"><?= number_format((int) $stats['learning']); ?></strong>
                                <p class="mb-0 text-secondary small">หัวข้อ</p>
                            </div>
                        </article>
                    </div>
                    <div class="col">
                        <article class="admin-stat-card stat-blue card h-100 border-0 shadow-sm position-relative">
                            <div class="card-body">
                                <span class="text-secondary small fw-bold">คุณสมบัติ</span>
                                <strong class="d-block fs-2 lh-1 my-2"><?= number_format((int) $stats['qualification']); ?></strong>
                                <p class="mb-0 text-secondary small">รายการ</p>
                            </div>
                        </article>
                    </div>
                    <div class="col">
                        <article class="admin-stat-card stat-red card h-100 border-0 shadow-sm position-relative">
                            <div class="card-body">
                                <span class="text-secondary small fw-bold">เอกสาร</span>
                                <strong class="d-block fs-2 lh-1 my-2"><?= number_format((int) $stats['document']); ?></strong>
                                <p class="mb-0 text-secondary small">รายการ</p>
                            </div>
                        </article>
                    </div>
                    <div class="col">
                        <article class="admin-stat-card card h-100 border-0 shadow-sm position-relative">
                            <div class="card-body">
                                <span class="text-secondary small fw-bold">หมายเหตุ</span>
                                <strong class="d-block fs-2 lh-1 my-2"><?= number_format((int) $stats['note']); ?></strong>
                                <p class="mb-0 text-secondary small">รายการ</p>
                            </div>
                        </article>
                    </div>
                </section>

                <section class="row g-4">
                    <div class="col-12">
                        <article class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white d-flex align-items-center justify-content-between gap-3">
                                <h3 class="h5 mb-0">รายการรายละเอียด</h3>
                                <div class="d-flex flex-wrap gap-2">
                                    <a class="btn btn-outline-primary btn-sm" href="<?= site_url('admin/course-details/'.$course->id); ?>">รีเฟรช</a>
                                    <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#courseDetailFormModal">
                                        เพิ่มรายละเอียด
                                    </button>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>ลำดับ</th>
                                                <th>ประเภท</th>
                                                <th>หัวข้อ</th>
                                                <th>รายละเอียด</th>
                                                <th>จัดการ</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($details)): ?>
                                                <tr>
                                                    <td colspan="5" class="text-center text-secondary py-4">ยังไม่มีรายละเอียดหลักสูตร</td>
                                                </tr>
                                            <?php endif; ?>

                                            <?php foreach ($details as $detail): ?>
                                                <?php
                                                $section_type = (string) $detail->section_type;
                                                $section_label = isset($section_labels[$section_type]) ? $section_labels[$section_type] : $section_type;
                                                ?>
                                                <tr>
                                                    <td><?= number_format((int) $detail->sort_order); ?></td>
                                                    <td>
                                                        <span class="badge <?= isset($section_classes[$section_type]) ? $section_classes[$section_type] : 'text-bg-secondary'; ?>">
                                                            <?= html_escape($section_label); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <strong class="d-block"><?= html_escape($detail->title ?: '-'); ?></strong>
                                                    </td>
                                                    <td><?= nl2br(html_escape($detail->content ?: '-')); ?></td>
                                                    <td>
                                                        <div class="d-flex flex-wrap gap-2">
                                                            <a class="btn btn-sm btn-outline-primary" href="<?= site_url('admin/course-details/'.$course->id.'?edit='.$detail->id); ?>">แก้ไข</a>
                                                            <form class="js-course-detail-delete-form" method="post" action="<?= site_url('admin/course-details/delete/'.$detail->id); ?>" data-detail-title="<?= html_escape($detail->title ?: $section_label); ?>">
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

                <div class="modal fade" id="courseDetailFormModal" tabindex="-1" aria-labelledby="courseDetailFormModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <form method="post" action="<?= $form_action; ?>">
                                <div class="modal-header bg-primary text-white">
                                    <h2 class="modal-title h5" id="courseDetailFormModalLabel"><?= $is_edit ? 'แก้ไขรายละเอียด' : 'เพิ่มรายละเอียด'; ?></h2>
                                    <a class="btn-close" href="<?= site_url('admin/course-details/'.$course->id); ?>" aria-label="Close"></a>
                                </div>

                                <div class="modal-body">
                                    <div class="row g-3">
                                        <div class="col-md-8">
                                            <label class="form-label fw-bold" for="section_type">ประเภท</label>
                                            <select class="form-select" id="section_type" name="section_type" required>
                                                <?php foreach ($section_labels as $value => $label): ?>
                                                    <option value="<?= html_escape($value); ?>" <?= $edit_detail->section_type === $value ? 'selected' : ''; ?>>
                                                        <?= html_escape($label); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label fw-bold" for="sort_order">ลำดับ</label>
                                            <input class="form-control" id="sort_order" type="number" name="sort_order" value="<?= (int) $edit_detail->sort_order; ?>">
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label fw-bold" for="title">หัวข้อ</label>
                                            <input class="form-control" id="title" type="text" name="title" value="<?= $is_edit ? html_escape($edit_detail->title) : ''; ?>">
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label fw-bold" for="content">รายละเอียด</label>
                                            <textarea class="form-control" id="content" name="content" rows="5"><?= $is_edit ? html_escape($edit_detail->content) : ''; ?></textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="modal-footer">
                                    <a class="btn btn-outline-dark" href="<?= site_url('admin/course-details/'.$course->id); ?>">ยกเลิก</a>
                                    <button class="btn btn-primary" type="submit">
                                        <?= $is_edit ? 'บันทึกการแก้ไข' : 'เพิ่มรายละเอียด'; ?>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <?php if ($is_edit): ?>
                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            var modal = new bootstrap.Modal(document.getElementById('courseDetailFormModal'));
                            modal.show();
                        });
                    </script>
                <?php endif; ?>
                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                <script>
                    document.querySelectorAll('.js-course-detail-delete-form').forEach(function (form) {
                        form.addEventListener('submit', function (event) {
                            event.preventDefault();

                            Swal.fire({
                                title: 'ยืนยันการลบรายละเอียด?',
                                text: 'ต้องการลบ "' + form.dataset.detailTitle + '" ใช่หรือไม่',
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
