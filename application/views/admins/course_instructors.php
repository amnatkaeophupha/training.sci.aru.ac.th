<?php
$this->load->view('admins/layouts/header');
$this->load->view('admins/layouts/sidebar');

$course_instructors = isset($course_instructors) ? $course_instructors : array();
$instructors = isset($instructors) ? $instructors : array();
$course = isset($course) && is_object($course) ? $course : (object) array(
    'id' => NULL,
    'title' => '',
    'slug' => ''
);
$edit_link = isset($edit_link) && is_object($edit_link) ? $edit_link : (object) array(
    'id' => NULL,
    'course_id' => $course->id,
    'instructor_id' => '',
    'role' => 'วิทยากรหลัก',
    'sort_order' => 0
);
$stats = array_merge(
    array(
        'total' => 0,
        'main' => 0
    ),
    isset($stats) && is_array($stats) ? $stats : array()
);

$is_edit = !empty($edit_link->id);
$form_action = $is_edit
    ? site_url('admin/course-instructors/update/'.$edit_link->id)
    : site_url('admin/course-instructors/store/'.$course->id);
?>
        <div class="admin-content">
            <header class="admin-topbar d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 px-3 px-lg-4 py-3 border-bottom">
                <div>
                    <h1 class="h4 mb-1">วิทยากรประจำหลักสูตร</h1>
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

                <?php if (empty($instructors)): ?>
                    <div class="alert alert-warning fw-semibold" role="alert">
                        กรุณาเพิ่มข้อมูลวิทยากรที่สถานะใช้งานก่อนเชื่อมกับหลักสูตร
                    </div>
                <?php endif; ?>

                <section class="row row-cols-1 row-cols-md-2 g-3 mb-4" aria-label="สรุปวิทยากรประจำหลักสูตร">
                    <div class="col">
                        <article class="admin-stat-card card h-100 border-0 shadow-sm position-relative">
                            <div class="card-body">
                                <span class="text-secondary small fw-bold">วิทยากรในหลักสูตร</span>
                                <strong class="d-block fs-2 lh-1 my-2"><?= number_format((int) $stats['total']); ?></strong>
                                <p class="mb-0 text-secondary small">รายการเชื่อมโยง</p>
                            </div>
                        </article>
                    </div>
                    <div class="col">
                        <article class="admin-stat-card stat-green card h-100 border-0 shadow-sm position-relative">
                            <div class="card-body">
                                <span class="text-secondary small fw-bold">วิทยากรหลัก</span>
                                <strong class="d-block fs-2 lh-1 my-2"><?= number_format((int) $stats['main']); ?></strong>
                                <p class="mb-0 text-secondary small">นับจากบทบาทที่มีคำว่าหลัก</p>
                            </div>
                        </article>
                    </div>
                </section>

                <section class="row g-4">
                    <div class="col-12">
                        <article class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white d-flex align-items-center justify-content-between gap-3">
                                <h3 class="h5 mb-0">รายชื่อวิทยากร</h3>
                                <div class="d-flex flex-wrap gap-2">
                                    <a class="btn btn-outline-primary btn-sm" href="<?= site_url('admin/course-instructors/'.$course->id); ?>">รีเฟรช</a>
                                    <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#courseInstructorFormModal" <?= empty($instructors) ? 'disabled' : ''; ?>>
                                        เพิ่มวิทยากร
                                    </button>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>ลำดับ</th>
                                                <th>วิทยากร</th>
                                                <th>บทบาท</th>
                                                <th>ติดต่อ</th>
                                                <th>สถานะ</th>
                                                <th>จัดการ</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($course_instructors)): ?>
                                                <tr>
                                                    <td colspan="6" class="text-center text-secondary py-4">ยังไม่มีวิทยากรในหลักสูตรนี้</td>
                                                </tr>
                                            <?php endif; ?>

                                            <?php foreach ($course_instructors as $item): ?>
                                                <?php
                                                $is_active = (int) $item->is_active === 1;
                                                $initial = function_exists('mb_substr') ? mb_substr($item->instructor_name, 0, 1, 'UTF-8') : substr($item->instructor_name, 0, 1);
                                                ?>
                                                <tr>
                                                    <td><?= number_format((int) $item->sort_order); ?></td>
                                                    <td>
                                                        <div class="d-flex align-items-center gap-3">
                                                            <?php if (!empty($item->photo)): ?>
                                                                <img class="rounded-circle object-fit-cover" src="<?= base_url($item->photo); ?>" alt="<?= html_escape($item->instructor_name); ?>" width="52" height="52">
                                                            <?php else: ?>
                                                                <span class="admin-avatar flex-shrink-0"><?= html_escape($initial); ?></span>
                                                            <?php endif; ?>
                                                            <div>
                                                                <strong class="d-block"><?= html_escape($item->instructor_name ?: '-'); ?></strong>
                                                                <span class="small text-secondary"><?= html_escape($item->position ?: $item->department ?: '-'); ?></span>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td><span class="badge text-bg-primary"><?= html_escape($item->role ?: '-'); ?></span></td>
                                                    <td>
                                                        <?php if (!empty($item->email)): ?>
                                                            <a class="d-block" href="mailto:<?= html_escape($item->email); ?>"><?= html_escape($item->email); ?></a>
                                                        <?php else: ?>
                                                            <span class="d-block text-secondary">-</span>
                                                        <?php endif; ?>
                                                        <?php if (!empty($item->phone)): ?>
                                                            <span class="small text-secondary"><?= html_escape($item->phone); ?></span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge <?= $is_active ? 'text-bg-success' : 'text-bg-secondary'; ?>">
                                                            <?= $is_active ? 'ใช้งาน' : 'ปิดใช้งาน'; ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex flex-wrap gap-2">
                                                            <a class="btn btn-sm btn-outline-primary" href="<?= site_url('admin/course-instructors/'.$course->id.'?edit='.$item->id); ?>">แก้ไข</a>
                                                            <form class="js-course-instructor-delete-form" method="post" action="<?= site_url('admin/course-instructors/delete/'.$item->id); ?>" data-instructor-name="<?= html_escape($item->instructor_name ?: 'วิทยากร'); ?>">
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

                <div class="modal fade" id="courseInstructorFormModal" tabindex="-1" aria-labelledby="courseInstructorFormModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <form method="post" action="<?= $form_action; ?>">
                                <div class="modal-header bg-primary text-white">
                                    <h2 class="modal-title h5" id="courseInstructorFormModalLabel"><?= $is_edit ? 'แก้ไขวิทยากรของหลักสูตร' : 'เพิ่มวิทยากรให้หลักสูตร'; ?></h2>
                                    <a class="btn-close" href="<?= site_url('admin/course-instructors/'.$course->id); ?>" aria-label="Close"></a>
                                </div>

                                <div class="modal-body">
                                    <div class="row g-3">
                                        <div class="col-md-8">
                                            <label class="form-label fw-bold" for="instructor_id">วิทยากร</label>
                                            <select class="form-select" id="instructor_id" name="instructor_id" required>
                                                <option value="">เลือกวิทยากร</option>
                                                <?php foreach ($instructors as $instructor): ?>
                                                    <option value="<?= (int) $instructor->id; ?>" <?= (int) $edit_link->instructor_id === (int) $instructor->id ? 'selected' : ''; ?>>
                                                        <?= html_escape($instructor->name); ?><?= !empty($instructor->department) ? ' - '.html_escape($instructor->department) : ''; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label fw-bold" for="sort_order">ลำดับ</label>
                                            <input class="form-control" id="sort_order" type="number" name="sort_order" value="<?= (int) $edit_link->sort_order; ?>">
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label fw-bold" for="role">บทบาท</label>
                                            <input class="form-control" id="role" type="text" name="role" value="<?= $is_edit ? html_escape($edit_link->role) : 'วิทยากรหลัก'; ?>" list="courseInstructorRoleOptions" placeholder="เช่น วิทยากรหลัก">
                                            <datalist id="courseInstructorRoleOptions">
                                                <option value="วิทยากรหลัก">
                                                <option value="ผู้ช่วยวิทยากร">
                                                <option value="ผู้รับผิดชอบหลักสูตร">
                                            </datalist>
                                        </div>
                                    </div>
                                </div>

                                <div class="modal-footer">
                                    <a class="btn btn-outline-dark" href="<?= site_url('admin/course-instructors/'.$course->id); ?>">ยกเลิก</a>
                                    <button class="btn btn-primary" type="submit" <?= empty($instructors) ? 'disabled' : ''; ?>>
                                        <?= $is_edit ? 'บันทึกการแก้ไข' : 'เพิ่มวิทยากร'; ?>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <?php if ($is_edit): ?>
                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            var modal = new bootstrap.Modal(document.getElementById('courseInstructorFormModal'));
                            modal.show();
                        });
                    </script>
                <?php endif; ?>
                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                <script>
                    document.querySelectorAll('.js-course-instructor-delete-form').forEach(function (form) {
                        form.addEventListener('submit', function (event) {
                            event.preventDefault();

                            Swal.fire({
                                title: 'ยืนยันการลบวิทยากรออกจากหลักสูตร?',
                                text: 'ต้องการลบ "' + form.dataset.instructorName + '" ออกจากหลักสูตรนี้ใช่หรือไม่',
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
